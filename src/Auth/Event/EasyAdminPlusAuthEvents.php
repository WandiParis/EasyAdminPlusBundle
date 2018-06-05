<?php

namespace Lle\EasyAdminPlusBundle\Auth\Event;

final class EasyAdminPlusAuthEvents
{
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_CREATE = 'lle.easy_admin_plus.user_pre_create';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_CREATE = 'lle.easy_admin_plus.user_post_create';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_REMOVE = 'lle.easy_admin_plus.user_pre_remove';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_REMOVE = 'lle.easy_admin_plus.user_post_remove';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_ENABLE = 'lle.easy_admin_plus.user_pre_enable';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_ENABLE = 'lle.easy_admin_plus.user_post_enable';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_DISABLE = 'lle.easy_admin_plus.user_pre_disable';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_DISABLE = 'lle.easy_admin_plus.user_post_disable';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_UPDATE_ROLES = 'lle.easy_admin_plus.user_pre_update_roles';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_UPDATE_ROLES = 'lle.easy_admin_plus.user_post_update_roles';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_CHANGE_PASSWORD = 'lle.easy_admin_plus.user_pre_update_password';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_CHANGE_PASSWORD = 'lle.easy_admin_plus.user_post_update_password';
}
