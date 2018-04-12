<?php

namespace Wandi\EasyAdminPlusBundle\Translator\Event;

final class EasyAdminPlusTranslatorEvents
{
    // Events related to custom action
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const PRE_TRANSLATE = 'wandi.easy_admin_plus.pre_translate';
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    const POST_TRANSLATE = 'wandi.easy_admin_plus.post_translate';
}
