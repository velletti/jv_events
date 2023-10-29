<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace JVE\JvEvents\ViewHelpers\Security;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * This ViewHelper implements an ifHasRoles/else condition for frontend groups.
 *
 * Examples
 * ========
 *
 * Basic usage
 * -----------
 *
 * ::
 *
 *    <jve:security.ifHasRoles roles="'Administrator','SuperAdmin'" mode="any">
 *        This is being shown in case the current FE user belongs to any or All Roles FE usergroup (aka role) depending on mode titled "Administrator" (case sensitive)
 *        roles will be exploded
 *    </jve:security.ifHasRoles>
 *
 * Everything inside the :html:`<f:security.ifHasRoles>` tag is being displayed if the
 * logged in frontend user belongs to the specified frontend user group.
 * Comparison is done by comparing to title of the user groups.
 *
 * Using the usergroup uid as role identifier also posible
 * ------------------------------------------
 *
 * ::
 *
 *    <jve:security.ifHasRole roles="1,3" mode="all">
 *       This is being shown in case the current FE user belongs to all listed FE usergroup (aka role) with  uid "1" and "3"
 *    </jve:security.ifHasRole>
 *
 * Everything inside the :html:`<jve:security.ifHasRole>` tag is being displayed if the
 * logged in frontend user belongs to the specified role. Comparison is done
 * using the ``uid`` of frontend user groups.
 *
 * IfRole / then / else
 * --------------------
 *
 * ::
 *
 *    <jve:security.ifHasRoles roles="1,2" mode="all">
 *       <f:then>
 *          This is being shown in case you have BOTH  roles .
 *       </f:then>
 *       <f:else>
 *          This is being displayed in case you do not have the needed roles.
 *       </f:else>
 *    </jve:security.ifHasRole>
 *
 * Everything inside the :html:`<f:then></f:then>` tag is displayed if the logged in FE user belongs to the specified role.
 * Otherwise, everything inside the :html:`<f:else></f:else>` tag is displayed.
 */
class IfHasRolesViewHelper extends AbstractConditionViewHelper
{
    /**
     * Initializes the "role" argument.
     * Renders <f:then> child if the current logged in FE user belongs to the specified role (aka usergroup)
     * otherwise renders <f:else> child.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('roles', 'string', 'string, comma separated with usergroups (either the usergroup uid or its title).');
        $this->registerArgument('mode', 'string', 'Mode : any or all default is any.', false , 'any');
    }

    /**
     * This method decides if the condition is TRUE or FALSE. It can be overridden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexibility in overriding this method.
     * @return bool
     */
    protected static function evaluateCondition($arguments = null)
    {
        $roles = GeneralUtility::trimExplode( "," , $arguments['roles'] );
        $allRoles = $arguments['mode'] == "all";

        /** @var UserAspect $userAspect */
        $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('frontend.user');
        if (!$userAspect->isLoggedIn()) {
            return false;
        }
        $groupIds = $userAspect->getGroupIds();
        $groupNames = $userAspect->getGroupNames();
        $return = $allRoles ;
        foreach ( $roles as $role ) {
            $role = trim($role , "'") ;
            if ( $allRoles ) {
                if( MathUtility::canBeInterpretedAsInteger($role) ) {
                    if ( !in_array((int)$role, $groupIds, true)) {
                        $return = false;
                    }
                } else {
                    if( !in_array( $role, $groupNames , true ) ){
                        $return = false;
                    }
                }
            } else {
                if( MathUtility::canBeInterpretedAsInteger($role) ) {
                    if(  in_array((int)$role, $groupIds, true)) {
                        return true;
                    }
                } else {
                    if( in_array( $role, $groupNames, true)){
                        return true;
                    }
                }
            }
        }
        return $return ;
    }
}
