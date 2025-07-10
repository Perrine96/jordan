<?php

namespace App\Controller\Admin;

use App\Entity\Products;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use App\Enum\ProductTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class ProductsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Products::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('model'),
            ChoiceField::new('type')
            ->setChoices([
                'Clothes' => ProductTypeEnum::CLOTHES,
                'Shoes' => ProductTypeEnum::SHOES,
            ])
            ->renderAsBadges([
                ProductTypeEnum::CLOTHES->value => 'success',
                ProductTypeEnum::SHOES->value => 'info',
            ])
            ->formatValue(fn ($value, $entity) => $value?->value),
            MoneyField::new('price')
                ->setCurrency('USD')
                ->setStoredAsCents(false),
            IntegerField::new('quantity'),
            TextField::new('image'),
    ];
    }
}
