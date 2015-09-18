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
 * PriceController
 *
 * @copyright   Cloudrexx AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_pim
 */

namespace Cx\Modules\Pim\Controller;

/**
 *
 * PriceController for displaying all the prices.
 *
 * @copyright   Cloudrexx AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_pim
 */
class PriceController extends \Cx\Core\Core\Model\Entity\Controller
{

    /**
     * Em instance
     * @var \Doctrine\ORM\EntityManager em
     */
    protected $em;

    /**
     * Sigma template instance
     * @var \Cx\Core\Html\Sigma $template
     */
    protected $template;

    /**
     * ProductRepository instance
     * @var \Cx\Modules\Pim\Model\Repository\PriceRepository $priceRepository
     */
    protected $priceRepository;

    /**
     * module name
     * @var string $moduleName
     */
    public $moduleName = 'Pim';

    /**
     * module name for language placeholder
     * @var string $moduleNameLang
     */
    public $moduleNameLang = 'PIM';

    /**
     * Controller for the Backend Orders views
     *
     * @param \Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController the system component controller object
     * @param \Cx\Core\Core\Controller\Cx $cx the cx object
     * @param \Cx\Core\Html\Sigma $template the template object
     */
    public function __construct(\Cx\Core\Core\Model\Entity\SystemComponentController $systemComponentController, \Cx\Core\Core\Controller\Cx $cx)
    {
        parent::__construct($systemComponentController, $cx);

        $this->em = $this->cx->getDb()->getEntityManager();
        $this->priceRepository = $this->em->getRepository('Cx\Modules\Pim\Model\Entity\Price');
    }

    public function parsePage(\Cx\Core\Html\Sigma $template)
    {
        $this->template = $template;

        $this->showPrices();
    }

    public function showPrices()
    {
        global $_ARRAYLANG;

        $prices = $this->priceRepository->findAll();
        if (empty($prices)) {
            $prices = new \Cx\Modules\Pim\Model\Entity\Price();
        }

        $view = new \Cx\Core\Html\Controller\ViewGenerator($prices, array(
            'header' => $_ARRAYLANG['TXT_MODULE_PIM_ACT_PRICE'],
            'validate' => function ($formGenerator) {
                // this validation checks whether already a price for the currency and product exists
                $data = $formGenerator->getData()->toArray();

                $currency = $data['currency'];
                $product = $data['product'];
                $priceRepository = \Env::get('cx')->getDb()->getEntityManager()->getRepository('Cx\Modules\Pim\Model\Entity\Price');
                $prices =
                    $priceRepository->createQueryBuilder('p')
                    ->where('p.currency = ?1')->setParameter(1, $currency)
                    ->andWhere('p.product = ?2')->setParameter(2, $product);
                $prices = $prices->getQuery()->getResult();
                if (!empty($data['editid']) && count($prices) > 1) {
                    return false;
                }
                if (empty($data['editid']) && count($prices) > 0) {
                    return false;
                }
                return true;
            },
            'functions' => array(
                'add' => true,
                'edit' => true,
                'delete' => true,
                'sorting' => true,
                'paging' => true,
                'filtering' => false,
            ),
        ));
        $this->template->setVariable('PRICES_CONTENT', $view->render());
    }
}