<?php

namespace Lle\EasyAdminPlusBundle\Exporter\Event;

final class EasyAdminPlusExporterEvents
{
    // Events related to backend views
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const PRE_EXPORT = 'lle.easy_admin_plus.pre_export';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const POST_EXPORT = 'lle.easy_admin_plus.post_export';
}
