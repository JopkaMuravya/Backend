<?php

namespace App\Controller\Admin;

use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookingCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Бронирование')
            ->setEntityLabelInPlural('Бронирования')
            ->setSearchFields(['comment', 'status'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('guest', 'Гость'))
            ->add(EntityFilter::new('house', 'Дом'))
            ->add(ChoiceFilter::new('status', 'Статус')
                ->setChoices([
                    'Ожидание' => 'pending',
                    'Подтверждено' => 'confirmed',
                    'Отменено' => 'cancelled',
                    'Завершено' => 'completed'
                ])
            )
            ->add(DateTimeFilter::new('createdAt', 'Дата создания'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $confirmAction = Action::new('confirm', 'Подтвердить', 'fa fa-check')
            ->linkToUrl(function (Booking $booking) {
                return '/admin/booking/' . $booking->getId() . '/confirm';
            })
            ->displayIf(fn(Booking $booking) => $booking->getStatus() === 'pending');

        $cancelAction = Action::new('cancel', 'Отменить', 'fa fa-times')
            ->linkToUrl(function (Booking $booking) {
                return '/admin/booking/' . $booking->getId() . '/cancel';
            })
            ->displayIf(fn(Booking $booking) => $booking->getStatus() !== 'cancelled');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_INDEX, $confirmAction)
            ->add(Crud::PAGE_INDEX, $cancelAction)
            ->add(Crud::PAGE_DETAIL, $confirmAction)
            ->add(Crud::PAGE_DETAIL, $cancelAction);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        
        yield AssociationField::new('guest', 'Гость')
            ->autocomplete()
            ->setCrudController(UserCrudController::class);
        
        yield AssociationField::new('house', 'Дом')
            ->autocomplete()
            ->setCrudController(HouseCrudController::class);
        
        yield TextareaField::new('comment', 'Комментарий')
            ->hideOnIndex();
        
        yield ChoiceField::new('status', 'Статус')
            ->setChoices([
                'Ожидание' => 'pending',
                'Подтверждено' => 'confirmed',
                'Отменено' => 'cancelled',
                'Завершено' => 'completed'
            ])
            ->renderAsBadges([
                'pending' => 'warning',
                'confirmed' => 'success',
                'cancelled' => 'danger',
                'completed' => 'info'
            ]);
        
        yield DateTimeField::new('createdAt', 'Создано')
            ->setFormat('dd.MM.yyyy HH:mm')
            ->onlyOnIndex();
        
        yield DateTimeField::new('updatedAt', 'Обновлено')
            ->setFormat('dd.MM.yyyy HH:mm')
            ->onlyOnDetail();
        
        if ($pageName === Crud::PAGE_INDEX) {
            yield TextField::new('guest.email', 'Email гостя')
                ->onlyOnIndex()
                ->formatValue(function ($value, Booking $booking) {
                    return $booking->getGuest()->getEmail();
                });
            
            yield TextField::new('house.name', 'Название дома')
            ->onlyOnIndex()
            ->formatValue(function ($value, Booking $booking) {
                return $booking->getHouse()->getName();

            });
        }
        
        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('guest.fullName', 'Гость (полное имя)')
                ->onlyOnDetail()
                ->formatValue(function ($value, Booking $booking) {
                    return $booking->getGuest()->getFullName();
                });
            
            yield TextField::new('guest.phone', 'Телефон гостя')
                ->onlyOnDetail()
                ->formatValue(function ($value, Booking $booking) {
                    return $booking->getGuest()->getPhone();
                });
            
            yield MoneyField::new('house.pricePerNight', 'Цена дома за ночь')
                ->onlyOnDetail()
                ->setCurrency('RUB')
                ->setNumDecimals(0)
                ->setStoredAsCents(false)
                ->formatValue(function ($value, Booking $booking) {
                    return $booking->getHouse()->getPricePerNight();
                });
        }
    }

    #[Route('/admin/booking/{id}/confirm', name: 'admin_booking_confirm')]
    public function confirmBookingAction(Request $request, int $id): RedirectResponse
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($id);
        
        if (!$booking) {
            $this->addFlash('error', 'Бронирование не найдено');
            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => self::class,
                'crudAction' => 'index',
            ]);
        }
        
        $booking->setStatus('confirmed');
        
        $this->entityManager->flush();
        
        $this->addFlash('success', '✅ Бронирование успешно подтверждено');
        
        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('admin', [
            'crudControllerFqcn' => self::class,
            'crudAction' => 'index',
        ]));
    }
    
    #[Route('/admin/booking/{id}/cancel', name: 'admin_booking_cancel')]
    public function cancelBookingAction(Request $request, int $id): RedirectResponse
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($id);
        
        if (!$booking) {
            $this->addFlash('error', 'Бронирование не найдено');
            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => self::class,
                'crudAction' => 'index',
            ]);
        }
        
        $booking->setStatus('cancelled');
        
        $this->entityManager->flush();
        
        $this->addFlash('warning', '❌ Бронирование отменено администратором');
        
        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('admin', [
            'crudControllerFqcn' => self::class,
            'crudAction' => 'index',
        ]));
    }
}
