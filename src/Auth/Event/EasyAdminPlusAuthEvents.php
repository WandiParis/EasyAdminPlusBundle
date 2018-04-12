<?php

namespace Wandi\EasyAdminPlusBundle\Auth\Event;

final class EasyAdminPlusAuthEvents
{
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_CREATE = 'wandi.easy_admin_plus.user_pre_create';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_CREATE = 'wandi.easy_admin_plus.user_post_create';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_REMOVE = 'wandi.easy_admin_plus.user_pre_remove';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_REMOVE = 'wandi.easy_admin_plus.user_post_remove';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_ENABLE = 'wandi.easy_admin_plus.user_pre_enable';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_ENABLE = 'wandi.easy_admin_plus.user_post_enable';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_DISABLE = 'wandi.easy_admin_plus.user_pre_disable';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_DISABLE = 'wandi.easy_admin_plus.user_post_disable';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_UPDATE_ROLES = 'wandi.easy_admin_plus.user_pre_update_roles';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_UPDATE_ROLES = 'wandi.easy_admin_plus.user_post_update_roles';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_PRE_CHANGE_PASSWORD = 'wandi.easy_admin_plus.user_pre_update_password';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const USER_POST_CHANGE_PASSWORD = 'wandi.easy_admin_plus.user_post_update_password';
}
