<?php

namespace App\Http\Controllers;

use App\Models\Chef;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\Cost;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MyDashboardController extends Controller
{
    public function index(Request $request)
    {
       
        //  return Sale::where('order_status', 1)->get();
        $start = $request->start_date;
        $end = $request->end_date;

        // তারিখ ভ্যালিডেশন
        if ($start && $end && Carbon::parse($start)->gt(Carbon::parse($end))) {
            return redirect()->route('mydashboard')->with('error', 'শুরুর তারিখ শেষ তারিখের চেয়ে বড় হতে পারবে না');
        }

         // ১. মোট লাভ গণনা (Gross Profit)
        $today_gross_profit = $this->calculateProfit($this->getSalesWithItems('today'));
        $month_gross_profit = $this->calculateProfit($this->getSalesWithItems('month'));
        $year_gross_profit = $this->calculateProfit($this->getSalesWithItems('year'));
        $all_time_gross_profit = $this->calculateProfit($this->getSalesWithItems('all'));

        // ২. মোট খরচ গণনা
        $today_expense = $this->getExpenseByPeriod('today');
        $month_expense = $this->getExpenseByPeriod('month');
        $year_expense = $this->getExpenseByPeriod('year');
        $all_time_expense = $this->getExpenseByPeriod('all');

        // ৩. প্রকৃত লাভ গণনা (Net Profit = Gross Profit - Expense)
        $today_net_profit = $today_gross_profit - $today_expense;
        $month_net_profit = $month_gross_profit - $month_expense;
        $year_net_profit = $year_gross_profit - $year_expense;
        $all_time_net_profit = $all_time_gross_profit - $all_time_expense;

        // ড্যাশবোর্ড ডেটা সংগ্রহ
        $data = [
            // KPI মেট্রিক্স
            'today_sales' => $this->getTodaySales(),
            'weekly_sales' => $this->getWeeklySales(),
            'monthly_sales' => $this->getMonthlySales(),
            'all_sales' => $this->getAllSales(),
            'today_order' => $this->getTodayOrders(),
            'weekly_order' => $this->getWeeklyOrders(),
            'monthly_order' => $this->getMonthlyOrders(),
            'all_order' => $this->getAllOrders(),
            'today_expense' => $this->getTodayExpense(),
            'weekly_expense' => $this->getWeeklyExpense(),
            'total_expense' => $this->getTotalExpense(),
            'month_expense' => $this->getMonthExpense(),
            
            // গণনা ডেটা
            'total_customers' => Customer::count(),
            'total_products' => Product::count(),
            'total_dealers' => Dealer::count(),
            'total_chefs' => Chef::count(),
            'total_users' => User::count(),
            'total_due' => $this->getAllDue(),
            'customer_due' => $this->getCustomerDue(),
            'dealer_due' => $this->getDealerDue(),
            
            // লাভ হিসাব
            'today_profit' => $this->getTodaySales() - $this->getTodayExpense(),
            'weekly_profit' => $this->getWeeklySales() - $this->getWeeklyExpense(),
            'monthly_profit' => $this->getMonthlySales() - $this->getMonthExpense(),
            'total_profit' => $this->getAllSales() - $this->getTotalExpense(),
            'today_profit_actual' => $today_net_profit,
            'month_profit_actual' => $month_net_profit,
            'year_profit_actual' => $year_net_profit,
            'all_time_profit_actual' => $all_time_net_profit, // নতুন ভেরিয়েবল
            
            // টেবিল ডেটা
            'latest_orders' => $this->getLatestOrder($start, $end),
            'latest_sales' => $this->getLatestSales($start, $end),
            'latest_customers' => Customer::latest()->take(5)->get(),
            'latest_dealers' => Dealer::latest()->take(5)->get(),
            'low_stock_products' => Product::where('quantity', '<', 50)->orderBy('quantity')->get(),
            
            // চার্ট ডেটা
            'months' => $this->getMonthLabels(),
            'sales_data' => $this->getMonthlySalesData(),
            'customer_growth' => $this->getCustomerGrowth(),
            'dealer_growth' => $this->getDealerGrowth(),
            'payment_methods' => $this->getPaymentMethodStats(),
            'top_products' => $this->getTopProducts(),
            'monthly_sales_chart' => $this->getMonthlySalesChart(),
            
            // মালিকদের ডেটা
            'owners' => Owner::all(),
        ];

        return view('mydeshboard', $data);
    }

    // ========== Private Helper Methods ==========

    /**
     * আজকের মোট বিক্রয়।
     */
    private function getTodaySales()
    {
        return Sale::where('order_status', 1)
            ->whereDate('created_at', Carbon::today())
            ->sum('total');
    }

        
    private function getWeeklySales()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // সপ্তাহের শুরু যদি মাসের শুরুর আগে হয়, তাহলে মাসের শুরু থেকে গণনা করা হবে
        $startDate = $startOfWeek->greaterThan($startOfMonth) ? $startOfWeek : $startOfMonth;

        return Sale::where('order_status', 1)
            ->whereDate('created_at', '>=', $startDate->toDateString())
            ->whereDate('created_at', '<=', Carbon::now()->endOfWeek()->toDateString())
            ->sum('total');
    }

    
    private function getMonthlySales()
    {
        return Sale::where('order_status', 1)
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth()->toDateString())
            ->whereDate('created_at', '<=', Carbon::now()->endOfMonth()->toDateString())
            ->sum('total');
    }

     private function getAllSales()
    {
        return Sale::where('order_status', 1)->sum('total');
    }

    private function getTodayOrders()
    {
        return Sale::where('order_status', 0)
            ->whereDate('created_at', Carbon::today())
            ->sum('total');
    }

    /**
     * এই সপ্তাহের পেন্ডিং অর্ডারের মোট পরিমাণ।
     * whereBetween এর পরিবর্তে whereDate ব্যবহার করে এটি আরও নির্ভরযোগ্য করা হয়েছে।
     */
    
        private function getWeeklyOrders()
    {
        // সপ্তাহের শুরুর তারিখ এবং মাসের শুরুর তারিখ বের করা
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // সপ্তাহের শুরু যদি মাসের শুরুর আগে হয়, তাহলে মাসের শুরু থেকে গণনা করা হবে
        // অন্যথায়, সপ্তাহের শুরু থেকেই গণনা করা হবে
        $startDate = $startOfWeek->greaterThan($startOfMonth) ? $startOfWeek : $startOfMonth;

        return Sale::where('order_status', 0)
            ->whereBetween('created_at', [
                $startDate,
                Carbon::now()->endOfWeek() // সপ্তাহের শেষ ঠিক থাকবে
            ])
            ->sum('total');
    }

    /**
     * এই মাসের পেন্ডিং অর্ডারের মোট পরিমাণ।
     * whereBetween এর পরিবর্তে whereDate ব্যবহার করে এটি আরও নির্ভরযোগ্য করা হয়েছে।
     */
    private function getMonthlyOrders()
    {
        return Sale::where('order_status', 0)
            ->whereDate('created_at', '>=', Carbon::now()->startOfMonth()->toDateString())
            ->whereDate('created_at', '<=', Carbon::now()->endOfMonth()->toDateString())
            ->sum('total');
    }
    private function getAllOrders()
    {
        return Sale::where('order_status', 0)->sum('total');
    }
    private function getTodayExpense()
    {
        return Cost::whereDate('created_at', Carbon::today())->sum('amount');
    }
    private function getWeeklyExpense()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // সপ্তাহের শুরু যদি মাসের শুরুর আগে হয়, তাহলে মাসের শুরু থেকে গণনা করা হবে
        $startDate = $startOfWeek->greaterThan($startOfMonth) ? $startOfWeek : $startOfMonth;

        return Cost::whereDate('created_at', '>=', $startDate->toDateString())
                ->whereDate('created_at', '<=', Carbon::now()->endOfWeek()->toDateString())
                ->sum('amount');
    }


    private function getMonthExpense()
    {
        return Cost::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->toDateString())
                ->whereDate('created_at', '<=', Carbon::now()->endOfMonth()->toDateString())
                ->sum('amount');
    }

        private function getTotalExpense()
    {
        return Cost::sum('amount');
    }

    private function getAllDue()
    {
        return Sale::where('order_status','=',0)->sum('due');
    }

    private function getCustomerDue()
    {
        return Sale::where('order_status','=',0)->where('customer_id','!=',null)->sum('due');
    }

    private function getDealerDue()
    {
        return Sale::where('order_status','=',0)->where('dealer_id','!=',null)->sum('due');
    }


    
    private function getSalesWithItems($period)
    {
        $query = Sale::where('order_status', 1)->with('items.product');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'month':
                $query->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month);
                break;
            case 'year':
                // *** এই অংশটি যুক্ত করুন ***
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'all':
                break;
        }
        return $query->latest()->get(); 
    } 

    private function calculateProfit($sales)
    {
        return $sales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                $purchase = $item->product->purchase_price ?? 0;
                return ($item->price - $purchase) * $item->quantity;
            });
        });
    }

        private function getExpenseByPeriod($period)
    {
        $query = Cost::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'month':
                $query->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'all':
                // কোনো শর্ত নেই
                break;
        }

        return $query->sum('amount');
    }

    private function getLatestOrder($start, $end)
    {
        $query = Sale::with('customer', 'dealer')->where('order_status', 0);

        if ($start && $end) {
            $query->whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        }

        return $query->latest()->take(10)->get();
    } 
    private function getLatestSales($start, $end)
    {
        $query = Sale::with('customer', 'dealer')->where('order_status', 1);

        if ($start && $end) {
            $query->whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        }

        return $query->latest()->take(10)->get();
    }

    private function getMonthLabels()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create()->month($i)->format('M');
        }
        return $months;
    }

    private function getMonthlySalesData()
    {
        $salesData = [];
        for ($i = 1; $i <= 12; $i++) {
            $salesData[] = Sale::where('order_status', 1)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', $i)
                ->sum('total');
        }
        return $salesData;
    }

    private function getCustomerGrowth()
    {
        return Customer::select(
            DB::raw("DATE_FORMAT(created_at,'%Y-%m') as month"),
            DB::raw("COUNT(*) as total")
        )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
    }

    private function getDealerGrowth()
    {
        return Dealer::select(
            DB::raw("DATE_FORMAT(created_at,'%Y-%m') as month"),
            DB::raw("COUNT(*) as total")
        )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
    }

    private function getPaymentMethodStats()
    {
        return Sale::where('order_status', 0)
            ->select('payment_method', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');
    }

    private function getTopProducts()
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as qty'))
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->pluck('qty', 'products.name');
    }

    private function getMonthlySalesChart()
    {
        return Sale::where('order_status', 1)
            ->select(
                DB::raw("DATE_FORMAT(created_at,'%Y-%m') as month"),
                DB::raw("SUM(total) as total")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
    }
}