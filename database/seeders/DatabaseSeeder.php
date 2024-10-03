<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AppSettingsTableSeeder::class,
            RoleTableSeeder::class,
            UsersTableSeeder::class,
            ModelHasRolesTableSeeder::class,
            PermissionTableSeeder::class,
            RoleHasPermissionsTableSeeder::class,
            ModelHasPermissionsTableSeeder::class,
            CountriesTableSeeder::class,
            StatesTableSeeder::class,
            CitiesTableSeeder::class,
            BookingStatusesTableSeeder::class,
            SettingsTableSeeder::class,
            ProviderTypesTableSeeder::class,
            HandymanTypesTableSeeder::class,
            PlansTableDataSeeder::class,
            StaticDataSeeder::class,
            CategoriesTableSeeder::class,
            SubCategoriesTableSeeder::class,
            ServicePackagesTableSeeder::class,
            SlidersTableSeeder::class,
            PostRequestStatusesTableSeeder::class,
            BlogsTableSeeder::class,
            TaxesTableSeeder::class,
            BanksTableSeeder::class,
            ProviderPayoutsTableSeeder::class,
            DocumentsTableSeeder::class,
            ProviderTaxesTableSeeder::class,
            ProviderSlotMappingsTableSeeder::class,
            PlanLimitsTableSeeder::class,
            PaymentGatewaysTableSeeder::class,
            CouponsTableSeeder::class,
            AppDownloadsTableSeeder::class,
            WalletsTableSeeder::class,
            WalletHistoriesTableSeeder::class,
            PermissionSeederV2::class,
            RoleHasPermissionSeederV2::class,
            BadgesTableSeeder::class,
            DepartmentSeeder::class,
            TypeSeeder::class,
            ShiftTypesSeeder::class,
            PriceTypesSeeder::class,
            MaterialUnitsSeeder::class,
            ShiftHoursSeeder::class,
            CertificatesSeeder::class,
            ProviderDocumentsTableSeeder::class,
        ]);
    }
}
