<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->app["events"]->listen(BuildingMenu::class, function (
            BuildingMenu $event
        ) {
            $user = auth()->user();

            if ($user && $user->role == 1) {
                // 🔹 Admin সব মেনু দেখতে পারবে
                $event->menu->add(
                    [
                        "type" => "fullscreen-widget",
                        "topnav_right" => true,
                    ],

                    [
                        "type" => "sidebar-menu-search",
                        "text" => "search",
                    ],
                    [
                        "text" => "ড্যাশবোর্ড",
                        "url" => "/my-dashboard",
                        "icon" => "fas fa-fw fa-tachometer-alt", // ড্যাশবোর্ডের জন্য উপযুক্ত আইকন
                    ],
                    [
                        "text" => "নতুন অপশন", 
                        "icon" => "fas fa-fw fa-credit-card", // কার্ডের জন্য আইকন
                        "submenu" => [
                            [
                                "text" => "প্রতিদিনের কার্ডের হিসাব", // আরও স্পষ্ট নাম
                                "url" => "/claimed-cards",
                                "icon" => "fas fa-fw fa-receipt",
                            ],
                            [
                                "text" => "প্রতিদিনের বিক্রয়ের হিসাব", // আরও স্পষ্ট নাম
                                "url" => "/sales-reportall",
                                "icon" => "fas fa-fw fa-receipt",
                            ],
                            [
                                "text" => "প্রতিদিনের জমার হিসাব", // আরও স্পষ্ট নাম
                                "url" => "/paymentReport",
                                "icon" => "fas fa-fw fa-receipt",
                            ], 
                            [
                                "text" => "স্টক মুভমেন্ট লগ", // আরও স্পষ্ট নাম
                                "url" => "/stock-movements",
                                "icon" => "fas fa-fw fa-receipt",
                            ], 
                            
                        ],
                    ],
                    [
                        "text" => "পণ্যভিত্তিক বিক্রয় বিশ্লেষণ",
                        "url"  => "/reports/product-sales-analysis",
                        "icon" => "fas fa-chart-bar text-primary",
                    ],
                    [
                        "text" => "পণ্য ব্যবস্থাপনা", // 'পোডাক্ট ম্যানেজমেন্ট' এর পরিবর্তে
                        "url" => "/products4",
                        "icon" => "fas fa-fw fa-boxes", // পণ্যের জন্য উপযুক্ত আইকন
                    ],
                    [
                        "text" => "বিক্রয় ব্যবস্থাপনা", // 'বিক্রয় ম্যানেজমেন্ট' এর পরিবর্তে
                        "icon" => "fas fa-fw fa-shopping-cart",
                        "submenu" => [
                            [
                                "text" => "নতুন খুচরা বিক্রয় (TP)", // আরও স্পষ্ট নাম
                                "url" => "/pos",
                                "icon" => "fas fa-fw fa-cash-register", // POS এর জন্য আইকন
                            ],
                            [
                                "text" => "নতুন ডিলার বিক্রয়(DP)", // আরও স্পষ্ট নাম
                                "url" => "/dealer-sales",
                                "icon" => "fas fa-fw fa-truck-loading", // ডিলারের জন্য আইকন
                            ],
                            [
                                "text" => "ডিপো বিক্রয়",
                                "url" => "/dipo-sales",
                                "icon" => "fas fa-fw fa-warehouse",
                            ],
                            [
                                "text" => "নতুন অর্ডার তালিকা", // 'সব অর্ডার দেখুন' এর পরিবর্তে
                                "url" => "/sales/new-orders",
                                "icon" => "fas fa-fw fa-hourglass-start", // পেন্ডিং অর্ডারের জন্য আইকন
                            ],
                            [
                                "text" => "পেন্ডিং অর্ডার তালিকা", // 'সব অর্ডার দেখুন' এর পরিবর্তে
                                "url" => "/sales/pending",
                                "icon" => "fas fa-fw fa-hourglass-start", // পেন্ডিং অর্ডারের জন্য আইকন
                            ],
                            [
                                "text" => "কনফার্ম অর্ডার তালিকা",
                                "url" => "/sales/confrim",
                                "icon" => "fas fa-fw fa-clipboard-check",
                            ],
                        ],
                    ],
                    [
                        "text" => "বকেয়া হিসাব",
                        "icon" => "fas fa-balance-scale", // ⚖️ হিসাব/ব্যালান্স আইকন
                        "submenu" => [
                            [
                                "text" => "কাস্টমার বকেয়া তালিকা",
                                "url" => "/allcustomerdues",
                                "icon" => "fas fa-file-invoice-dollar", // 💰 Invoice/Dues icon
                            ],
                            [
                                "text" => "ডিলারের বকেয়া তালিকা",
                                "url" => "/alldealerdues",
                                "icon" => "fas fa-file-invoice-dollar", // 💰 Invoice/Dues icon
                            ],

                            [
                                "text" => "ডিপু কাস্টমার বকেয়া তালিকা",
                                "url" => "/alldcustomerdues",
                                "icon" => "fas fa-file-invoice-dollar", // 💰 Invoice/Dues icon
                            ],
                            
                        ],
                    ],

                    [
                        "text" => "কাস্টমার তালিকা", // 'কাষ্টমার ম্যানেজমেন্ট' এর পরিবর্তে
                        "url" => "/customers",
                        "icon" => "fas fa-fw fa-users", // কাস্টমারের জন্য আইকন
                    ],
                    [
                        "text" => "ডিলার তালিকা", // 'ডিলার ম্যানেজমেন্ট' এর পরিবর্তে
                        "url" => "/dealers",
                        "icon" => "fas fa-fw fa-handshake", // ডিলারের জন্য আইকন
                    ],
                    [
                        "text" => "ডিপু কাস্টমার তালিকা", // 'কাষ্টমার ম্যানেজমেন্ট' এর পরিবর্তে
                        "url" => "/dcustomers",
                        "icon" => "fas fa-fw fa-users", // কাস্টমারের জন্য আইকন
                    ],

                    [
                        "text" => "খরচ ব্যবস্থাপনা", // 'খরচ ম্যানেজমেন্ট' এর পরিবর্তে
                        "url" => "/cost",
                        "icon" => "fas fa-fw fa-wallet", // খরচের জন্য আইকন
                    ],
                    [
                        "text" => "কার্ড ব্যবস্থাপনা", // 'কার্ড ম্যানেজমেন্ট' এর পরিবর্তে
                        "icon" => "fas fa-fw fa-credit-card", // কার্ডের জন্য আইকন
                        "submenu" => [
                            [
                                "text" => "নতুন কার্ড তৈরি",
                                "url" => "/cards/create",
                                "icon" => "fas fa-fw fa-plus-circle",
                            ],
                            [
                                "text" => "কার্ড যাচাইকরণ", // 'কার্ড চেক করোন' এর পরিবর্তে
                                "url" => "/cards/check",
                                "icon" => "fas fa-fw fa-search-dollar",
                            ],
                            [
                                "text" => "ক্লেইমড কার্ড তালিকা", // আরও স্পষ্ট নাম
                                "url" => "/payment-claim",
                                "icon" => "fas fa-fw fa-receipt",
                            ],
                            
                        ],
                    ],
                    [
                        "text" => "শেফ ব্যবস্থাপনা", // 'বাবুর্চি ম্যানেজমেন্ট' এর পরিবর্তে
                        "url" => "/chef/create",
                        "icon" => "fas fa-fw fa-utensils", // শেফের জন্য উপযুক্ত আইকন
                    ],
                    [
                        "text" => "খোলা পণ্য ম্যানেজমেন্ট",
                        "icon" => "fas fa-box-open text-success", // মূল মেনুর জন্য আইকন
                        "submenu" => [
                            [
                                "text" => "পণ্য ও স্টক", // 'পণ্য যোগ করুন' এর পরিবর্তে এটি আরও সঠিক
                                "url" => "/morich/products",
                                "icon" => "fas fa-boxes text-info", // পণ্য তালিকার জন্য আইকন
                            ],
                            [
                                "text" => "বিক্রয় এন্ট্রি করুন", // 'করোন' এর পরিবর্তে 'করুন'
                                "url" => "/morich/daily-sale/create",
                                "icon" => "fas fa-cart-plus text-info", // বিক্রয় যোগ করার আইকন
                            ],
                            [
                                "text" => "বিক্রয় রিপোর্ট দেখুন",
                                "url" => "/morich/daily-sale/reports",
                                "icon" => "fas fa-chart-line text-info", // রিপোর্টের জন্য আইকন
                            ],
                            [
                                "text" => "স্টক রিপোর্ট",
                                "url" => "/morich/products/stock-report", // অথবা route('morich.products.stock-report')
                                "icon" => "fas fa-warehouse text-info",
                            ],
                            [
                                "text" => "পণ্যভিত্তিক বিক্রয়",
                                "url" => "/morich/sreport",
                                "icon" => "fas fa-chart-pie text-info",
                            ],
                            [
                                "text" => "স্টক এন্ট্রির ইতিহাস",
                                "url"  => "/morich/stock-entries",
                                "icon" => "fas fa-history text-info",
                            ],
                            [
                                "text" => "স্টক মুভমেন্ট",
                                "url"  => "/morich/stock-movement",
                                "icon" => "fas fa-exchange-alt text-info",
                            ],
                            [
                                "text" => "তারিখভিত্তিক লেজার",
                                "url"  => "/morich/date-wise-ledger",
                                "icon" => "fas fa-book text-info",
                            ],
                            [
                                "text" => "কাস্টমার বকেয়া তালিকা",
                                "url" => "/custom/customers",
                                "icon" => "fas fa-book-open text-primary",
                            ],

                        ],
                    ],

                    [
                        "header" => "সিস্টেম ও ব্যবহারকারী", // একটি হেডার দিয়ে সেকশন আলাদা করা হলো
                    ],
                    [
                        "text" => "নতুন ব্যবহারকারী", // 'নতুন ইউজার তৈরী করোন' এর পরিবর্তে
                        "url" => "/acount/create",
                        "icon" => "fas fa-fw fa-user-plus", // নতুন ব্যবহারকারীর জন্য আইকন
                    ],
                    [
                        "text" => "এসআর বকেয়া হিসাব",
                        "icon" => "fas fa-file-invoice-dollar", // 💰 বকেয়া হিসাব
                        "submenu" => [
                            [
                                "text" => "এসআর গ্রহন করা বকেয়া",
                                "url" => "/srcustomers-dues",
                                "icon" => "fas fa-users", // 👥 গ্রাহক/কাস্টমার
                            ],
                            [
                                "text" => "এসআর এর কনফার্ম হওয়া বকেয়া",
                                "url" => "/srdealers-dues",
                                "icon" => "fas fa-user-tie", // 👔 ডিলার/ব্যবসায়ী
                            ],
                        ],
                    ]
                );
            }

            if ($user && $user->role == 0) {
                // 🔹 User শুধু এই মেনু দেখতে পারবে
                $event->menu->add(
                    [
                        "type" => "fullscreen-widget",
                        "topnav_right" => true,
                    ],
                    [
                        // ১. কাষ্টমার ম্যানেজমেন্ট
                        "text" => "কাস্টমার তালিকা", // নাম পরিবর্তন করা হয়েছে
                        "url" => "/customers",
                        "icon" => "fas fa-fw fa-users", // কাস্টমারদের জন্য উপযুক্ত আইকন
                    ],
                    [
                        // ২. কাস্টমারের কাছে পোডাক্ট বিক্রয় করোন
                        "text" => "নতুন খুচরা বিক্রয় (TP)", // নাম আরও স্পষ্ট করা হয়েছে
                        "url" => "/pos",
                        "icon" => "fas fa-fw fa-cash-register", // POS সিস্টেমের জন্য সঠিক আইকন
                    ],
                    [
                                "text" => "নতুন অর্ডার তালিকা", // 'সব অর্ডার দেখুন' এর পরিবর্তে
                                "url" => "/sales/new-orders",
                                "icon" => "fas fa-fw fa-hourglass-start", // পেন্ডিং অর্ডারের জন্য আইকন
                    ],
                    [
                        // ৩. পেন্ডিং সব অর্ডার দেখুন
                        "text" => "পেন্ডিং অর্ডার তালিকা", // নামটি আরও আনুষ্ঠানিক করা হয়েছে
                        "url" => "/sales/pending",
                        "icon" => "fas fa-fw fa-hourglass-half", // পেন্ডিং অর্ডারের জন্য উপযুক্ত আইকন
                    ],
                     [
                                "text" => "কনফার্ম অর্ডার তালিকা",
                                "url" => "/sales/confrim",
                                "icon" => "fas fa-fw fa-clipboard-check",
                            ],
                    [
                        "text" => "কাস্টমার বকেয়া তালিকা",
                        "url" => "/allcustomerdues",
                        "icon" => "fas fa-file-invoice-dollar", // 💰 Invoice/Dues icon
                    ],
                    [
                        // ৪. কার্ড চেক করোন
                        "text" => "কার্ড যাচাইকরণ", // নামটি আরও পেশাদার করা হয়েছে
                        "url" => "/cards/check",
                        "icon" => "fas fa-fw fa-search-dollar", // কার্ডের আর্থিক অবস্থা অনুসন্ধানের জন্য আইকন
                    ]
                );
            }

            if ($user && $user->role == 2) {
                $menuItems = [
                    [
                        "type" => "fullscreen-widget",
                        "topnav_right" => true,
                    ],

                    [
                        // ১. কাষ্টমার ম্যানেজমেন্ট
                        "text" => "কাস্টমার তালিকা",
                        "url" => "/customers",
                        "icon" => "fas fa-fw fa-users",
                    ],
                    [
                        "text" => "ডিলার তালিকা",
                        "url" => "/dealers",
                        "icon" => "fas fa-fw fa-handshake",
                    ],
                    [
                        "text" => "ডিপু কাস্টমার তালিকা", // 'কাষ্টমার ম্যানেজমেন্ট' এর পরিবর্তে
                        "url" => "/dcustomers",
                        "icon" => "fas fa-fw fa-users", // কাস্টমারের জন্য আইকন
                    ],
                    [
                        // ২. কাস্টমারের কাছে পোডাক্ট বিক্রয় করোন
                        "text" => "নতুন খুচরা বিক্রয় (TP)",
                        "url" => "/pos",
                        "icon" => "fas fa-fw fa-cash-register",
                    ],
                    [
                        "text" => "নতুন ডিলার বিক্রয়(DP)",
                        "url" => "/dealer-sales",
                        "icon" => "fas fa-fw fa-truck-loading",
                    ],
                    [
                                "text" => "ডিপো বিক্রয়",
                                "url" => "/dipo-sales",
                                "icon" => "fas fa-fw fa-warehouse",
                    ],
                    [
                                "text" => "নতুন অর্ডার তালিকা", // 'সব অর্ডার দেখুন' এর পরিবর্তে
                                "url" => "/sales/new-orders",
                                "icon" => "fas fa-fw fa-hourglass-start", // পেন্ডিং অর্ডারের জন্য আইকন
                            ],
                    [
                        // ৩. পেন্ডিং সব অর্ডার দেখুন
                        "text" => "পেন্ডিং অর্ডার তালিকা",
                        "url" => "/sales/pending",
                        "icon" => "fas fa-fw fa-hourglass-half",
                    ],
                     [
                                "text" => "কনফার্ম অর্ডার তালিকা",
                                "url" => "/sales/confrim",
                                "icon" => "fas fa-fw fa-clipboard-check",
                            ],
                    [
                        "text" => "কাস্টমার বকেয়া তালিকা",
                        "url" => "/allcustomerdues",
                        "icon" => "fas fa-fw fa-file-invoice-dollar",
                    ],
                    [
                        "text" => "ডিলারের বকেয়া তালিকা",
                        "url" => "/alldealerdues",
                        "icon" => "fas fa-fw fa-file-invoice-dollar",
                    ],
                    [
                                "text" => "ডিপু  বকেয়া তালিকা",
                                "url" => "/alldcustomerdues",
                                "icon" => "fas fa-file-invoice-dollar", // 💰 Invoice/Dues icon
                    ],
                    [
                        // ৪. কার্ড চেক করোন
                        "text" => "কার্ড যাচাইকরণ",
                        "url" => "/cards/check",
                        "icon" => "fas fa-fw fa-search-dollar",
                    ],
                ];

                if(url('/') == 'https://mb.gffoodandbeverage.com' && auth()->user()->id == 6){
                        
                         array_push($menuItems, 
                            [
                                "text" => "খরচ ব্যবস্থাপনা",
                                "url" => "/cost",
                                "icon" => "fas fa-fw fa-wallet",
                            ],
                        );
                }

                if($_ENV['APP_URL'] == 'https://kg.gffoodandbeverage.com' && auth()->user()->id == 2){
                        
                         array_push($menuItems, 
                            [
                                "text" => "পণ্য ব্যবস্থাপনা", // 'পোডাক্ট ম্যানেজমেন্ট' এর পরিবর্তে
                                "url" => "/products4",
                                "icon" => "fas fa-fw fa-boxes", // পণ্যের জন্য উপযুক্ত আইকন
                            ],
                        );
                }


                $event->menu->add(...$menuItems);              
            }

            if ($user && $user->role == 3) {
                // 🔹 User শুধু এই মেনু দেখতে পারবে
                $menuItems =[
                    [
                        "type" => "fullscreen-widget",
                        "topnav_right" => true,
                    ],
                    [                     
                        "text" => "কার্ড যাচাইকরণ", 
                        "url" => "/cards/check",
                        "icon" => "fas fa-fw fa-search-dollar", 
                    ],
                ];

                if($_ENV['APP_URL'] == 'https://app.gffoodandbeverage.com/' && auth()->user()->id === 19){
                        
                         array_push($menuItems, 
                            [
                                "text" => "ক্লিম হওয়া কার্ড", // 'পোডাক্ট ম্যানেজমেন্ট' এর পরিবর্তে
                                "url" => "/claimed-cards",
                                "icon" => "fas fa-fw fa-boxes", // পণ্যের জন্য উপযুক্ত আইকন
                            ],
                        );
                }



                $event->menu->add(...$menuItems);    



                // $event->menu->add([
                //     [
                //         "type" => "fullscreen-widget",
                //         "topnav_right" => true,
                //     ],
                //     // ৪. কার্ড চেক করোন
                //     "text" => "কার্ড যাচাইকরণ", // নামটি আরও পেশাদার করা হয়েছে
                //     "url" => "/cards/check",
                //     "icon" => "fas fa-fw fa-search-dollar", // কার্ডের আর্থিক অবস্থা অনুসন্ধানের জন্য আইকন
                // ]);
            }

            if ($user && $user->role == 10) {
                $event->menu->add(
                    
                    [
                        "text" => "পণ্য ও স্টক", // 'পণ্য যোগ করুন' এর পরিবর্তে এটি আরও সঠিক
                        "url" => "/morich/products",
                        "icon" => "fas fa-boxes text-info", // পণ্য তালিকার জন্য আইকন
                    ],
                    [
                        "text" => "বিক্রয় এন্ট্রি করুন", // 'করোন' এর পরিবর্তে 'করুন'
                        "url" => "/morich/daily-sale/create",
                        "icon" => "fas fa-cart-plus text-info",
                    ],
                    
                    [
                        "text" => "বিক্রয় রিপোর্ট দেখুন",
                        "url" => "/morich/daily-sale/reports",
                        "icon" => "fas fa-chart-line text-info", // রিপোর্টের জন্য আইকন
                    ],
                    [
                        "text" => "স্টক রিপোর্ট",
                        "url" => "/morich/products/stock-report", // অথবা route('morich.products.stock-report')
                        "icon" => "fas fa-warehouse text-info",
                    ],
                    [
                        "text" => "পণ্যভিত্তিক বিক্রয়",
                        "url" => "/morich/sreport",
                        "icon" => "fas fa-chart-pie text-info",
                    ], 
                    [
                        "text" => "স্টক এন্ট্রির ইতিহাস",
                        "url"  => "/morich/stock-entries",
                        "icon" => "fas fa-history text-info",
                    ],
                    [
                        "text" => "স্টক মুভমেন্ট",
                        "url"  => "/morich/stock-movement",
                        "icon" => "fas fa-exchange-alt text-info",
                    ],
                    [
                                "text" => "তারিখভিত্তিক লেজার",
                                "url"  => "/morich/date-wise-ledger",
                                "icon" => "fas fa-book text-info",
                    ],
                    [
                        "text" => "কাস্টমার বকেয়া তালিকা",
                        "url" => "/custom/customers",
                        "icon" => "fas fa-book-open text-primary",
                    ],
                    
                );
            }
        });
    }
}
