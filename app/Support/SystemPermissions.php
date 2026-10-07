<?php

namespace App\Support;

class SystemPermissions
{
    public static function all(): array
    {
        return array_merge([
            ['name' => 'view employee documents', 'display_name' => 'عرض مستندات الموظفين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'manage employee documents', 'display_name' => 'إدارة مستندات الموظفين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view dashboard', 'display_name' => 'عرض لوحة التحكم', 'group_name' => 'المنيو'],
            ['name' => 'view employee portal', 'display_name' => 'عرض بوابة الموظف', 'group_name' => 'بوابة الموظف'],
            ['name' => 'view employee management responses', 'display_name' => 'عرض ردود الإدارة', 'group_name' => 'بوابة الموظف'],
            ['name' => 'view employee complaints', 'display_name' => 'عرض شكاوى الموظف', 'group_name' => 'بوابة الموظف'],
            ['name' => 'view employee details', 'display_name' => 'عرض بيانات الموظف', 'group_name' => 'الموارد البشرية'],

            ['name' => 'view attendance fingerprints', 'display_name' => 'عرض البصمات', 'group_name' => 'الحضور'],
            ['name' => 'use attendance fingerprints', 'display_name' => 'استخدام بصمة الدخول والخروج', 'group_name' => 'الحضور'],
            ['name' => 'manage attendance fingerprints', 'display_name' => 'إدارة البصمات', 'group_name' => 'الحضور'],
            ['name' => 'create attendance fingerprints', 'display_name' => 'إضافة بصمة', 'group_name' => 'الحضور'],
            ['name' => 'edit attendance fingerprints', 'display_name' => 'تعديل بصمة', 'group_name' => 'الحضور'],
            ['name' => 'delete attendance fingerprints', 'display_name' => 'حذف بصمة', 'group_name' => 'الحضور'],
            ['name' => 'import attendance fingerprints', 'display_name' => 'استيراد البصمات', 'group_name' => 'الحضور'],
            ['name' => 'export attendance fingerprints', 'display_name' => 'تصدير البصمات', 'group_name' => 'الحضور'],
            ['name' => 'filter attendance fingerprints', 'display_name' => 'فلترة البصمات', 'group_name' => 'الحضور'],
            ['name' => 'view attendance absences', 'display_name' => 'عرض الغياب في البصمات', 'group_name' => 'الحضور'],
            ['name' => 'view one fingerprint records', 'display_name' => 'عرض سجلات البصمة الواحدة', 'group_name' => 'الحضور'],
            ['name' => 'view attendance leaves', 'display_name' => 'عرض الإجازات', 'group_name' => 'الحضور'],
            ['name' => 'manage attendance leaves', 'display_name' => 'إدارة الإجازات', 'group_name' => 'الحضور'],
            ['name' => 'create attendance leaves', 'display_name' => 'إضافة إجازة', 'group_name' => 'الحضور'],
            ['name' => 'edit attendance leaves', 'display_name' => 'تعديل إجازة', 'group_name' => 'الحضور'],
            ['name' => 'delete attendance leaves', 'display_name' => 'حذف إجازة', 'group_name' => 'الحضور'],
            ['name' => 'view attendance', 'display_name' => 'عرض الحضور', 'group_name' => 'الحضور'],
            ['name' => 'manage attendance', 'display_name' => 'إدارة الحضور', 'group_name' => 'الحضور'],

            ['name' => 'view structure centers', 'display_name' => 'عرض المراكز', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view structure departments', 'display_name' => 'عرض الأقسام', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view structure positions', 'display_name' => 'عرض المناصب', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view employees', 'display_name' => 'عرض الموظفين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'manage employees', 'display_name' => 'إدارة الموظفين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'create employees', 'display_name' => 'إضافة موظف', 'group_name' => 'الموارد البشرية'],
            ['name' => 'edit employees', 'display_name' => 'تعديل موظف', 'group_name' => 'الموارد البشرية'],
            ['name' => 'delete employees', 'display_name' => 'حذف موظف', 'group_name' => 'الموارد البشرية'],
            ['name' => 'manage employee accounts', 'display_name' => 'إدارة حسابات الموظفين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'manage employee roles', 'display_name' => 'إدارة أدوار الموظفين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'manage employee salaries', 'display_name' => 'إدارة رواتب الموظفين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'manage employee account access', 'display_name' => 'إدارة فروع الحسابات للموظفين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view inactive employees', 'display_name' => 'عرض الموظفين غير النشطين', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view discounts', 'display_name' => 'عرض الخصومات', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view salary report', 'display_name' => 'عرض تقرير الرواتب', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view holidays', 'display_name' => 'عرض العطلات', 'group_name' => 'الموارد البشرية'],
            ['name' => 'view statistics', 'display_name' => 'عرض الإحصائيات', 'group_name' => 'الموارد البشرية'],

            ['name' => 'view messages bulk', 'display_name' => 'عرض الرسائل الجماعية', 'group_name' => 'الرسائل'],
            ['name' => 'view messages personal', 'display_name' => 'عرض الرسائل الشخصية', 'group_name' => 'الرسائل'],
            ['name' => 'view complaints', 'display_name' => 'عرض الشكاوى', 'group_name' => 'الرسائل'],
            ['name' => 'manage complaints', 'display_name' => 'إدارة الشكاوى', 'group_name' => 'الرسائل'],
            ['name' => 'view employee requests', 'display_name' => 'عرض طلبات الموظفين', 'group_name' => 'الرسائل'],
            ['name' => 'review employee requests', 'display_name' => 'كتابة رد على طلبات الموظفين', 'group_name' => 'الرسائل'],
            ['name' => 'approve employee requests', 'display_name' => 'الموافقة على طلبات الموظفين', 'group_name' => 'الرسائل'],
            ['name' => 'reject employee requests', 'display_name' => 'رفض طلبات الموظفين', 'group_name' => 'الرسائل'],
            ['name' => 'manage management complaints', 'display_name' => 'إدارة شكاوى الإدارة', 'group_name' => 'الرسائل'],
            ['name' => 'view deleted documents', 'display_name' => 'عرض المستندات المحذوفة', 'group_name' => 'الرسائل'],

            ['name' => 'view customers', 'display_name' => 'عرض العملاء', 'group_name' => 'العملاء'],

            ['name' => 'view accounts maktoom', 'display_name' => 'دخول حسابات صالون المكتوم', 'group_name' => 'المشاريع - صالون المكتوم'],
            ['name' => 'view accounts avani', 'display_name' => 'دخول حسابات صالون أفاني', 'group_name' => 'المشاريع - صالون أفاني'],
            ['name' => 'view accounts perfumes', 'display_name' => 'دخول حسابات محل عطور أفاني', 'group_name' => 'المشاريع - عطور أفاني'],
            ['name' => 'view secret archive maktoom', 'display_name' => 'عرض الأرشيف السري - مكتوم', 'group_name' => 'الأرشيف السري'],
            ['name' => 'manage secret archive maktoom', 'display_name' => 'إدارة الأرشيف السري - مكتوم', 'group_name' => 'الأرشيف السري'],
            ['name' => 'view secret archive avani', 'display_name' => 'عرض الأرشيف السري - أفاني', 'group_name' => 'الأرشيف السري'],
            ['name' => 'manage secret archive avani', 'display_name' => 'إدارة الأرشيف السري - أفاني', 'group_name' => 'الأرشيف السري'],
            ['name' => 'manage accounts revenues', 'display_name' => 'إدارة الإيرادات', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'create backdated account revenues', 'display_name' => 'إضافة إيراد بتاريخ سابق', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'manage accounts expenses', 'display_name' => 'إدارة المصاريف', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'view accounts treasury', 'display_name' => 'عرض الخزنة والتقارير', 'group_name' => 'الحسابات - تقارير وخزنة'],
            ['name' => 'view employee revenues', 'display_name' => 'عرض إيرادات الموظفين', 'group_name' => 'الحسابات - تقارير وخزنة'],
            ['name' => 'view treasury audit', 'display_name' => 'عرض سجل الخزنة اليومي والتعديلات', 'group_name' => 'الحسابات - تقارير وخزنة'],

            ['name' => 'view settings users', 'display_name' => 'عرض حسابات المستخدمين', 'group_name' => 'الإعدادات'],
            ['name' => 'view settings roles', 'display_name' => 'عرض الأدوار', 'group_name' => 'الإعدادات'],
            ['name' => 'view settings permissions', 'display_name' => 'عرض الصلاحيات', 'group_name' => 'الإعدادات'],
            ['name' => 'view settings', 'display_name' => 'عرض الإعدادات', 'group_name' => 'الإعدادات'],
            ['name' => 'manage permissions', 'display_name' => 'إدارة الصلاحيات والأدوار', 'group_name' => 'الإعدادات'],
            ['name' => 'view logs', 'display_name' => 'عرض السجلات', 'group_name' => 'الإعدادات'],

            ['name' => 'view assets inventory', 'display_name' => 'عرض المخزون', 'group_name' => 'الأصول'],
            ['name' => 'manage assets inventory', 'display_name' => 'إدارة وإضافة المخزون', 'group_name' => 'الأصول'],
            ['name' => 'view assets categories', 'display_name' => 'عرض تصنيفات الأصول', 'group_name' => 'الأصول'],
            ['name' => 'view assets reports', 'display_name' => 'عرض تقارير الأصول', 'group_name' => 'الأصول'],

            ['name' => 'view salon services', 'display_name' => 'عرض خدمات الصالون', 'group_name' => 'المشاريع - صالونات أفاني والمكتوم'],
            ['name' => 'view salon invoices', 'display_name' => 'عرض فواتير الصالون', 'group_name' => 'المشاريع - صالونات أفاني والمكتوم'],

            ['name' => 'view maktoom dyes', 'display_name' => 'عرض مخزن صبغات مكتوم', 'group_name' => 'إدارة مكتوم'],
            ['name' => 'operate maktoom dyes', 'display_name' => 'تسليم وبيع صبغات مكتوم', 'group_name' => 'إدارة مكتوم'],
            ['name' => 'manage maktoom dyes', 'display_name' => 'إدارة وتوريد وجرد صبغات مكتوم', 'group_name' => 'إدارة مكتوم'],

            ['name' => 'toggle maintenance', 'display_name' => 'تشغيل وإيقاف وضع الصيانة', 'group_name' => 'الناف بار'],
            ['name' => 'view notifications', 'display_name' => 'عرض الإشعارات', 'group_name' => 'الناف بار'],
            ['name' => 'switch language', 'display_name' => 'تغيير اللغة', 'group_name' => 'الناف بار'],
            ['name' => 'switch theme', 'display_name' => 'تغيير النمط', 'group_name' => 'الناف بار'],
            ['name' => 'view profile menu', 'display_name' => 'عرض قائمة الحساب الشخصي', 'group_name' => 'الناف بار'],
        ], self::employeeRequestActionPermissions(), self::accountActionPermissions());
    }

    private static function employeeRequestActionPermissions(): array
    {
        return [
            ['name' => 'cancel employee requests', 'display_name' => 'إلغاء طلبات الموظفين', 'group_name' => 'الرسائل'],
        ];
    }

    private static function accountActionPermissions(): array
    {
        return [
            ['name' => 'create account revenues', 'display_name' => 'إضافة إيرادات', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'edit account revenues', 'display_name' => 'تعديل إيرادات اليوم الحالي', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'delete account revenues', 'display_name' => 'حذف إيرادات اليوم الحالي', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'edit backdated account revenues', 'display_name' => 'تعديل إيرادات الأيام السابقة', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'delete backdated account revenues', 'display_name' => 'حذف إيرادات الأيام السابقة', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'create account expenses', 'display_name' => 'إضافة مصاريف', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'create backdated account expenses', 'display_name' => 'إضافة مصاريف بتاريخ سابق', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'edit account expenses', 'display_name' => 'تعديل مصاريف اليوم الحالي', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'delete account expenses', 'display_name' => 'حذف مصاريف اليوم الحالي', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'edit backdated account expenses', 'display_name' => 'تعديل مصاريف الأيام السابقة', 'group_name' => 'الحسابات - إجراءات مشتركة'],
            ['name' => 'delete backdated account expenses', 'display_name' => 'حذف مصاريف الأيام السابقة', 'group_name' => 'الحسابات - إجراءات مشتركة'],
        ];
    }

    public static function projectPermissionPresets(): array
    {
        $accountActions = [
            'manage accounts revenues',
            'create account revenues',
            'edit account revenues',
            'delete account revenues',
            'create backdated account revenues',
            'edit backdated account revenues',
            'delete backdated account revenues',
            'manage accounts expenses',
            'create account expenses',
            'create backdated account expenses',
            'edit account expenses',
            'delete account expenses',
            'edit backdated account expenses',
            'delete backdated account expenses',
            'view accounts treasury',
        ];

        $base = [
            'view dashboard',
            'view profile menu',
            'view notifications',
            'switch language',
            'switch theme',
        ];

        return [
            'avani_salon' => [
                'label' => 'صالون أفاني',
                'description' => 'يفتح حسابات صالون أفاني وخدمات وفواتير الصالون مع إجراءات الإيرادات والمصاريف.',
                'permissions' => array_values(array_unique(array_merge($base, [
                    'view accounts avani',
                    'view salon services',
                    'view salon invoices',
                ], $accountActions))),
            ],
            'avani_perfumes' => [
                'label' => 'محل عطور أفاني',
                'description' => 'يفتح حسابات العطور والمخزون المرتبط بها مع إجراءات الإيرادات والمصاريف.',
                'permissions' => array_values(array_unique(array_merge($base, [
                    'view accounts perfumes',
                    'view assets inventory',
                    'view assets categories',
                    'view assets reports',
                ], $accountActions))),
            ],
            'maktoom_salon' => [
                'label' => 'صالون المكتوم',
                'description' => 'يفتح حسابات صالون المكتوم والصبغات وخدمات الصالون مع إجراءات الإيرادات والمصاريف.',
                'permissions' => array_values(array_unique(array_merge($base, [
                    'view accounts maktoom',
                    'view salon services',
                    'view salon invoices',
                    'view maktoom dyes',
                    'operate maktoom dyes',
                    'manage maktoom dyes',
                ], $accountActions))),
            ],
        ];
    }

    public static function roleDefaults(): array
    {
        $all = array_values(array_diff(
            array_column(self::all(), 'name'),
            self::primaryAdminControlledPermissions()
        ));
        $employee = [
            'view dashboard',
            'view employee portal',
            'view employee management responses',
            'view employee complaints',
            'view employee details',
            'view profile menu',
            'view notifications',
            'use attendance fingerprints',
            'switch language',
            'switch theme',
        ];
        $projectPresets = self::projectPermissionPresets();

        return [
            'Admin' => $all,
            'Employee' => $employee,
            'Viewer' => $employee,
            'AvaniSalonSales' => $projectPresets['avani_salon']['permissions'],
            'AvaniPerfumesSales' => $projectPresets['avani_perfumes']['permissions'],
            'MaktoomSalonSales' => $projectPresets['maktoom_salon']['permissions'],
            'CR' => ['view dashboard', 'view profile menu', 'switch language', 'switch theme'],
            'CC' => [
                'view dashboard',
                'view attendance leaves',
                'view messages bulk',
                'view profile menu',
                'view notifications',
                'switch language',
                'switch theme',
            ],
            'AM' => [
                'view dashboard',
                'view assets inventory',
                'manage assets inventory',
                'view assets categories',
                'view assets reports',
                'view profile menu',
                'view notifications',
                'switch language',
                'switch theme',
            ],
            'HR' => [
                'view dashboard',
                'view attendance',
                'manage attendance',
                'view attendance fingerprints',
                'manage attendance fingerprints',
                'create attendance fingerprints',
                'edit attendance fingerprints',
                'delete attendance fingerprints',
                'import attendance fingerprints',
                'export attendance fingerprints',
                'filter attendance fingerprints',
                'view attendance absences',
                'view one fingerprint records',
                'view attendance leaves',
                'manage attendance leaves',
                'create attendance leaves',
                'edit attendance leaves',
                'delete attendance leaves',
                'view structure centers',
                'view structure departments',
                'view structure positions',
                'view employees',
                'manage employees',
                'create employees',
                'edit employees',
                'delete employees',
                'manage employee accounts',
                'manage employee roles',
                'manage employee salaries',
                'manage employee account access',
                'view inactive employees',
                'view employee details',
                'view employee documents',
                'manage employee documents',
                'view messages bulk',
                'view messages personal',
                'view employee requests',
                'review employee requests',
                'approve employee requests',
                'reject employee requests',
                'cancel employee requests',
                'manage management complaints',
                'view deleted documents',
                'view discounts',
                'view holidays',
                'view statistics',
                'view assets reports',
                'toggle maintenance',
                'view profile menu',
                'view notifications',
                'switch language',
                'switch theme',
            ],
            'ManagementEmployee' => [
                'view dashboard',
                'view employee portal',
                'view employee management responses',
                'view employee complaints',
                'view employee details',
                'view employee requests',
                'view employee documents',
                'manage employee documents',
                'manage management complaints',
                'view deleted documents',
                'view discounts',
                'view customers',
                'view accounts maktoom',
                'view accounts avani',
                'view accounts perfumes',
                'manage accounts revenues',
                'create backdated account revenues',
                'manage accounts expenses',
                'view accounts treasury',
                'view employee revenues',
                'view assets inventory',
                'manage assets inventory',
                'view assets reports',
                'view salon services',
                'view salon invoices',
                'view maktoom dyes',
                'operate maktoom dyes',
                'view profile menu',
                'view notifications',
                'use attendance fingerprints',
                'switch language',
                'switch theme',
            ],
            'Accountant' => [
                'view dashboard',
                'view employee management responses',
                'view accounts maktoom',
                'view accounts avani',
                'view accounts perfumes',
                'manage accounts revenues',
                'manage accounts expenses',
                'view accounts treasury',
                'view assets inventory',
                'manage assets inventory',
                'view assets reports',
                'view maktoom dyes',
                'operate maktoom dyes',
                'view profile menu',
                'view notifications',
                'switch language',
                'switch theme',
            ],
        ];
    }

    public static function primaryAdminControlledPermissions(): array
    {
        return [
            'view settings roles',
            'view settings permissions',
            'manage permissions',
            'manage employee roles',
        ];
    }
}
