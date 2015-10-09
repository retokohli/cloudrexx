<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * VatRateController
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_order
 */

namespace Cx\Modules\Order\Controller;

/**
 * 
 * VatRateController for displaying all the vat rate.
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_order
 */
class VatRateController extends \Cx\Core\Core\Model\Entity\Controller {
    
    /**
     * Em instance
     * @var \Doctrine\ORM\EntityManager em
     */
    protected $em;
    
    /**
     * Sigma template instance
     * @var Cx\Core\Html\Sigma  $template
     */
    protected $template;
    
    /**
     * VatRateRepository instance 
     * @var \Cx\Modules\Order\Model\Repository\VatRateRepository $vatRateRepository
     */
    protected $vatRateRepository;

    /**
     * Controller for the backend vat rates views
     * 
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx                          $cx                        the cx object
     * @param \Cx\Core\Html\Sigma                                  $template                  the template object
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx) {
        parent::__construct($systemComponentController, $cx);
        
        $this->em                = $this->cx->getDb()->getEntityManager();
        $this->vatRateRepository = $this->em->getRepository('Cx\Modules\Order\Model\Entity\VatRate');
    }
    
    /**
     * Use this to parse your backend page
     * 
     * @param \Cx\Core\Html\Sigma $template 
     */
    public function parsePage(\Cx\Core\Html\Sigma $template) {
        $this->template = $template;
        
        $this->showVatRates();
    }
    
    public function showVatRates() 
    {
        global $_ARRAYLANG;
        
        $vatRates = $this->vatRateRepository->findAll();
        if (empty($vatRates)) {
            $vatRates = new \Cx\Modules\Order\Model\Entity\VatRate();
        }
        $view = new \Cx\Core\Html\Controller\ViewGenerator($vatRates, array(
            'header' => $_ARRAYLANG['TXT_MODULE_ORDER_ACT_VATRATE'],
            'fields' => array(
                'products'    => array(
                    'showOverview' => false,
                ),
                'rate'  => array(
                    'table' => array(
                        'parse' => function($value) {
                            if (empty($value)) {
                                return;
                            }
                            return $value . '%';
                        }
                    )
                )
            ),
            'functions' => array(
                'add'       => true,
                'edit'      => true,
                'delete'    => true,
                'sorting'   => true,
                'paging'    => true,
                'filtering' => false,
            ),
        ));
        $this->template->setVariable('VAT_RATE_CONTENT', $view->render());
    }
}
