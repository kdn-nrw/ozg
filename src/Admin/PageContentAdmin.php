<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2020 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Admin;

use App\Entity\PageContent;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\FormatterBundle\Form\Type\SimpleFormatterType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class PageContentAdmin extends AbstractAppAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('page', ChoiceType::class, [
                'choices' => [
                    'app.page_content.entity.page_choices.home' => PageContent::PAGE_HOME,
                    'app.page_content.entity.page_choices.service_systems' => PageContent::PAGE_SERVICE_SYSTEMS,
                    'app.page_content.entity.page_choices.services' => PageContent::PAGE_SERVICES,
                    'app.page_content.entity.page_choices.implementation_projects' => PageContent::PAGE_IMPLEMENTATION_PROJECTS,
                    'app.page_content.entity.page_choices.solutions' => PageContent::PAGE_SOLUTIONS,
                ],
                'required' => true,
            ])
            ->add('headline', TextType::class, [
                'required' => false,
            ])
            ->add('bodytext', SimpleFormatterType::class, [
                'format' => 'richhtml',
                'ckeditor_context' => 'default', // optional
                'required' => false,
            ])
            ->add('position', IntegerType::class);
        $formMapper->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $this->addFullTextDatagridFilter($datagridMapper);
        $datagridMapper->add('page');
        $datagridMapper->add('headline');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('createdAt')
            ->add('page', 'choice', [
                'editable' => true,
                'choices' => [
                    PageContent::PAGE_HOME => 'app.page_content.entity.page_choices.home',
                    PageContent::PAGE_SERVICE_SYSTEMS => 'app.page_content.entity.page_choices.service_systems',
                    PageContent::PAGE_SERVICES => 'app.page_content.entity.page_choices.services',
                    PageContent::PAGE_IMPLEMENTATION_PROJECTS => 'app.page_content.entity.page_choices.implementation_projects',
                    PageContent::PAGE_SOLUTIONS => 'app.page_content.entity.page_choices.solutions',
                ],
                'catalogue' => 'messages',
            ])
            ->add('headline')
            ->add('position');
        $this->addDefaultListActions($listMapper);
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('page')
            ->add('headline')
            ->add('bodytext');
    }
}
