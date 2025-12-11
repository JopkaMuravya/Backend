<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Пользователь')
            ->setEntityLabelInPlural('Пользователи')
            ->setSearchFields(['email', 'phone', 'firstName', 'lastName'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('email', 'Email'))
            ->add(TextFilter::new('phone', 'Телефон'))
            ->add(TextFilter::new('firstName', 'Имя'))
            ->add(TextFilter::new('lastName', 'Фамилия'))
            ->add(ChoiceFilter::new('roles', 'Роли')
                ->setChoices([
                    'Пользователь' => 'ROLE_USER',
                    'Администратор' => 'ROLE_ADMIN',
                    'Редактор' => 'ROLE_EDITOR',
                ])
                ->canSelectMultiple()
            );
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
        yield EmailField::new('email', 'Email');
        yield TextField::new('phone', 'Телефон');
        yield TextField::new('firstName', 'Имя');
        yield TextField::new('lastName', 'Фамилия');
        
        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            yield TextField::new('password', 'Пароль')
                ->setFormType(PasswordType::class)
                ->onlyOnForms()
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setHelp($pageName === Crud::PAGE_EDIT ? 'Оставьте пустым, если не хотите менять' : '');
        }
        
        yield ChoiceField::new('roles', 'Роли')
            ->setChoices([
                'Пользователь' => 'ROLE_USER',
                'Администратор' => 'ROLE_ADMIN',
                'Редактор' => 'ROLE_EDITOR',
            ])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->setHelp('Выберите одну или несколько ролей');
        
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('fullName', 'Полное имя')
                ->onlyOnIndex()
                ->formatValue(function ($value, User $user) {
                    return $user->getFullName();
                });
            
            yield ArrayField::new('roles', 'Роли (текст)')
                ->formatValue(function ($value) {
                    return implode(', ', array_map(function ($role) {
                        $roleNames = [
                            'ROLE_USER' => 'Пользователь',
                            'ROLE_ADMIN' => 'Администратор',
                            'ROLE_EDITOR' => 'Редактор',
                        ];
                        return $roleNames[$role] ?? $role;
                    }, $value));
                })
                ->onlyOnDetail();
        }
    }
}