<?php

namespace ilateral\SilverStripe\Users\Extensions;

use SilverStripe\Core\Extension;

/**
 * Extension for Controller that provide methods such as member management
 * interface to templates
 *
 * @package Users
 * @author  i-lateral <info@ilateral.co.uk>
 */
class ControllerExtension extends Extension
{
    /**
     * Render current user account nav
     *
     * @return string
     */
    public function getUserAccountNav()
    {
        return $this->owner->renderWith("ilateral\\SilverStripe\\Users\\Includes\\UserAccountNav");
    }
}