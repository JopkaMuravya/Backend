<?php

namespace App\Controller\Admin;

use App\Entity\House;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;

class HouseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return House::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Дом')
            ->setEntityLabelInPlural('Дома')
            ->setSearchFields(['name', 'amenities'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Название'))
            ->add(BooleanFilter::new('isAvailable', 'Доступен'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name', 'Название');
        
        yield MoneyField::new('pricePerNight', 'Цена за ночь')
            ->setCurrency('RUB')
            ->setNumDecimals(0)
            ->setStoredAsCents(false);
        
        yield IntegerField::new('capacity', 'Вместимость')
            ->setHelp('Максимальное количество гостей');
        
        yield IntegerField::new('distanceToSea', 'Расстояние до моря (м)')
            ->setHelp('Расстояние в метрах');
        
        yield TextareaField::new('amenities', 'Удобства')
            ->hideOnIndex();
        
        yield BooleanField::new('isAvailable', 'Доступен')
            ->renderAsSwitch(true);
    }
}