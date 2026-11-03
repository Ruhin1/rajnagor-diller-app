<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleZeroOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
         // যদি লগইন না থাকে তাহলে লগইন পেইজে পাঠানো হবে
        if (!auth()->check()) {
            return redirect()->route('login');
        }
 
          

        $allowedRoutes0 = [
            'homes',
            'pos.index',
            'r.posproducts',
            'r.poscustomers',
            'sales.store',
            'sales.show',
            'sales.pending',
            'sales.destroy',
            'sales.edit',
            'sales.update',
            'r.customerindex',
            'customers.store',
            'customers.update',
            'customers.destroy',
            'cards.index',
            'cards.check',
            'cards.process',
            'cards.searchChef',
            'allcustomerdues',
            'customers.dues',
            'customerDuesserch',
            'customers.pay-dues',
            'customers.quick.add',
            'dealers.quick.add',
            'customers.statement',
            'sales.pos.print',
            'sales.confrim',
            'sales.bluetooth.print',
            'customers.statement.bluetooth',
            'diller.statement.bluetooth',
            'sales.new_orders',
            'sales.delivery',
            
            

        ];
 
        $allowedRoutes2 = [
            'homes',
            'pos.index',
            'r.posproducts',
            'r.poscustomers',
            'sales.store',
            'sales.show',
            'sales.pending',
            'sales.destroy',
            'sales.edit',
            'sales.update',
            'r.customerindex',
            'customers.store',
            'customers.update',
            'customers.destroy',
            'cards.index',
            'cards.check',
            'cards.process',
            'cards.searchChef',
            'dealer.sales.page',
            'dealers.list',
            'dealer.sales.store',
            'dealer.show',
            'dealers.index',
            'dealers.store', 
            'dealers.update',
            'allcustomerdues',
            'dealerDuesserch',
            'customers.dues',
            'customerDuesserch',
            'customers.pay-dues',
            'alldealerdues',
            'dealer.dues',
            'dealerDuesserch',
            'dealer.pay-dues',
            'customers.quick.add',
            'dealers.quick.add',
            'customers.statement',
            'diller.statement', 
            'sales.pos.print',
            'sales.confrim',
            'sales.bluetooth.print',
            'customers.statement.bluetooth',
            'diller.statement.bluetooth',
            'dipo.sale',     
            'dipu.customer',   
            'sales.isdipu',
            'alldcustomerdues',
            'dr.customerindex',
            'dcustomers.store',
            'dcustomers.update',
            'dcustomers.destroy',
            'sales.new_orders',
            'sales.delivery',
            
        ]; 

        if (url('') == 'https://mb.gffoodandbeverage.com' && auth()->user()->id == 6)
        {
            $allowedRoutes2[]=  'cost.index';
            $allowedRoutes2[]=  'cost.store';
            $allowedRoutes2[]=  'cost.update';
            $allowedRoutes2[]=  'cost.destroy';     
                      
                 
        } 

        if ($_ENV['APP_URL'] == 'https://kg.gffoodandbeverage.com' && auth()->user()->id == 2)
        { 
            $allowedRoutes2[]=  'products.index';
            $allowedRoutes2[]=  'products.store';
            $allowedRoutes2[]=  'products.update';
            $allowedRoutes2[]=  'updateStock';
            $allowedRoutes2[]=  'updateStatus';                                           
        } 
        
        
        $allowedRoutes3 = [ 
            'homes',
            'cards.index',
            'cards.check',
            'cards.process',
            'cards.searchChef',
            
        ]; 

        if($_ENV['APP_URL'] == 'https://app.gffoodandbeverage.com/' && auth()->user()->id === 19){
             $allowedRoutes3[]=  'claimed.cards';
        }

        $allowedRoutes10 = [
            'homes',
            'morich.products.index',
            'morich.products.create',
            'morich.products.store',
            'morich.products.edit',
            'morich.products.update',
            'morich.products.destroy',

            'morich.products.stock-report',
            'morich.sreport',

            'morich.daily-sale.create',
            'morich.daily-sale.store',
            'morich.daily-sale.summary',
            'morich.daily-sale.reports',

            'custom.customers.index',
            'custom.customers.create',
            'custom.customers.store',
            'custom.transactions.store',
            'custom.customers.statement',

        ];

        if (auth()->user()->role == 0 && !in_array($request->route()->getName(), $allowedRoutes0)) {
            abort(403, 'You are not allowed to access this page.');
        }

        if (auth()->user()->role == 2 && !in_array($request->route()->getName(), $allowedRoutes2)) {
            abort(403, 'You are not allowed to access this page.');
        }

        if (auth()->user()->role == 3 && !in_array($request->route()->getName(), $allowedRoutes3)) {
            abort(403, 'You are not allowed to access this page.');
        }

         if (auth()->user()->role == 10 && !in_array($request->route()->getName(), $allowedRoutes10)) {
            abort(403, 'You are not allowed to access this page.');
        }


        return $next($request);
    }
}
