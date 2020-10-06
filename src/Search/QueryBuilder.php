<?php

/*
 * This file is part of the EasyAdminBundle.
 *
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lle\EasyAdminPlusBundle\Search;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Lle\EasyAdminPlusBundle\Filter\FilterState;
use Doctrine\Common\Collections\Criteria;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class QueryBuilder
{
    /** @var Registry */
    private $doctrine;
    private $request;
    private $filterState;
    private $entity;

    public function __construct(Registry $doctrine, RequestStack $requestStack, FilterState $filterState)
    {
        $this->doctrine = $doctrine;
        $this->request = $requestStack->getCurrentRequest();
        $this->entity = $this->request->query->get('entity');
        $this->filterState = $filterState;
    }

    /**
     * Creates the query builder used to get all the records displayed by the
     * "list" view.
     *
     * @param array       $entityConfig
     * @param string|null $sortField
     * @param string|null $sortDirection
     * @param string|null $dqlFilter
     *
     * @return DoctrineQueryBuilder
     */
    public function createListQueryBuilder(array $entityConfig, $sortField = null, $sortDirection = null, $dqlFilter = null, $request)
    {
        /* @var EntityManager */
        $em = $this->doctrine->getManagerForClass($entityConfig['class']);

        $disabled_filters = $entityConfig['disabled_filters'] ?? [];
        foreach($disabled_filters as $filter) {
            if($em->getFilters()->isEnabled($filter)){
                $em->getFilters()->disable($filter);
            }
        }
        /* @var DoctrineQueryBuilder */

        $qb_method = ($entityConfig['qb_method'] ?? null);
        if ($qb_method) {
            $queryBuilder = $em->getRepository($entityConfig['class'])->$qb_method();
        } else {
            $queryBuilder = $em->createQueryBuilder()
                ->select('entity')
                ->from($entityConfig['class'], 'entity')
            ;
        }

        // apply filters
        if (isset($entityConfig['filter'])) {
            $this->filterState->applyFilters($queryBuilder, $this->entity);
        }

        $isSortedByDoctrineAssociation = false !== strpos($sortField, '.');
        if ($isSortedByDoctrineAssociation) {
            $sortFieldParts = explode('.', $sortField);
            $queryBuilder->leftJoin($queryBuilder->getRootAlias().'.'.$sortFieldParts[0], $sortFieldParts[0]);
        }

        if (!empty($dqlFilter)) {
            $queryBuilder->andWhere($dqlFilter);
        }

        if (null !== $sortField) {
            $overwriteOrderBy = $entityConfig["overwrite_orderby"] ?? true;

            if ($overwriteOrderBy) {
                $queryBuilder->orderBy(sprintf('%s%s', $isSortedByDoctrineAssociation ? '' : $queryBuilder->getRootAlias() . '.', $sortField), $sortDirection);
            } else {
                $queryBuilder->addOrderBy(sprintf('%s%s', $isSortedByDoctrineAssociation ? '' : $queryBuilder->getRootAlias() . '.', $sortField), $sortDirection);
            }

            if(isset($entityConfig['list']) && isset($entityConfig['list']['sort']) && isset($entityConfig['list']['sort']['field'])) {
                $queryBuilder->addOrderBy(sprintf('%s%s',
                    $isSortedByDoctrineAssociation ? '' : $queryBuilder->getRootAlias().'.', $entityConfig['list']['sort']['field']), 'ASC');
            }
        }

        return $queryBuilder;
    }

    /**
     * Creates the query builder used to get the results of the search query
     * performed by the user in the "search" view.
     *
     * @param array       $entityConfig
     * @param string      $searchQuery
     * @param string|null $sortField
     * @param string|null $sortDirection
     * @param string|null $dqlFilter
     *
     * @return DoctrineQueryBuilder
     */
    public function createSearchQueryBuilder(array $entityConfig, $searchQuery, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        /* @var EntityManager */
        $em = $this->doctrine->getManagerForClass($entityConfig['class']);
        /* @var DoctrineQueryBuilder */
        
        $qb_method = ($entityConfig['qb_method'] ?? null);
        $qb_method_search = ($entityConfig['search']['qb_method'] ?? null);
        if ($qb_method_search) {
            $queryBuilder = $em->getRepository($entityConfig['class'])->$qb_method_search();
            $entityName = $queryBuilder->getRootAlias();
        } else if ($qb_method) {
            $queryBuilder = $em->getRepository($entityConfig['class'])->$qb_method();
            $entityName = $queryBuilder->getRootAlias();
        } else {
            $queryBuilder = $em->createQueryBuilder()
                ->select('entity')
                ->from($entityConfig['class'], 'entity')
            ;
            $entityName = 'entity';
        }

        $isSearchQueryNumeric = is_numeric($searchQuery);
        $isSearchQuerySmallInteger = (is_int($searchQuery) || ctype_digit($searchQuery)) && $searchQuery >= -32768 && $searchQuery <= 32767;
        $isSearchQueryInteger = (is_int($searchQuery) || ctype_digit($searchQuery)) && $searchQuery >= -2147483648 && $searchQuery <= 2147483647;
        $isSearchQueryUuid = 1 === preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $searchQuery);
        $lowerSearchQuery = mb_strtolower($searchQuery);

        $queryParameters = [];
        foreach($queryBuilder->getParameters() as $parameter) {
            $queryParameters[$parameter->getName()] = $parameter->getValue();
        }

        $entitiesAlreadyJoined = array();

        $orModule = $queryBuilder->expr()->orX();
        foreach ($entityConfig['search']['fields'] as $fieldName => $metadata) {
            if (false !== strpos($fieldName, '.')) {
                list($associatedEntityName, $associatedFieldName) = explode('.', $fieldName);
                if (!in_array($associatedEntityName, $entitiesAlreadyJoined)) {
                    $queryBuilder->leftJoin($entityName.'.'.$associatedEntityName, $associatedEntityName);
                    $entitiesAlreadyJoined[] = $associatedEntityName;
                }

                $entityName = $associatedEntityName;
                $fieldName = $associatedFieldName;
            }

            $isSmallIntegerField = 'smallint' === $metadata['dataType'];
            $isIntegerField = 'integer' === $metadata['dataType'];
            $isNumericField = in_array($metadata['dataType'], array('number', 'bigint', 'decimal', 'float'));
            $isTextField = in_array($metadata['dataType'], array('string', 'text'));
            $isGuidField = 'guid' === $metadata['dataType'];

            // this complex condition is needed to avoid issues on PostgreSQL databases
            if (
                $isSmallIntegerField && $isSearchQuerySmallInteger ||
                $isIntegerField && $isSearchQueryInteger ||
                $isNumericField && $isSearchQueryNumeric
            ) {
                $orModule->add(
                    $queryBuilder->expr()->eq(
                        sprintf('%s.%s', $entityName, $fieldName),
                        ':numeric_query')
                );

                // adding '0' turns the string into a numeric value
                $queryParameters['numeric_query'] = 0 + $searchQuery;
            } elseif ($isGuidField && $isSearchQueryUuid) {
                $orModule->add(
                    $queryBuilder->expr()->eq(
                        sprintf('%s.%s', $entityName, $fieldName),
                        ':uuid_query')
                );
                $queryParameters['uuid_query'] = $searchQuery;
            } elseif ($isTextField) {

                $orModule->add(
                    $queryBuilder->expr()->like(
                        sprintf('LOWER(%s.%s)', $entityName, $fieldName),
                        ':fuzzy_query')
                );
                $queryParameters['fuzzy_query'] = '%'.$lowerSearchQuery.'%';

            }
        }

        $queryBuilder->andWhere($orModule);

        if (0 !== count($queryParameters)) {
            $queryBuilder->setParameters($queryParameters);
        }

        if (!empty($dqlFilter)) {
            $queryBuilder->andWhere($dqlFilter);
        }

        $isSortedByDoctrineAssociation = false !== strpos($sortField, '.');
        if ($isSortedByDoctrineAssociation) {
            list($associatedEntityName, $associatedFieldName) = explode('.', $sortField);
            if (!in_array($associatedEntityName, $entitiesAlreadyJoined)) {
                $queryBuilder->leftJoin($entityName.'.'.$associatedEntityName, $associatedEntityName);
                $entitiesAlreadyJoined[] = $associatedEntityName;
            }
        }

        if (!$qb_method_search and null !== $sortField) {
            $queryBuilder->orderBy(sprintf('%s%s', $isSortedByDoctrineAssociation ? '' : $entityName.'.', $sortField), $sortDirection ?: 'DESC');
        }

        return $queryBuilder;
    }
}
