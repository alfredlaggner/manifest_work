<?php

	namespace App\ Http\ Controllers;

	use App\SaleInvoice;
    use App\SalesOrder;
	use Illuminate\ Http\ Request;
	use App\ Http\ Controllers\ Controller;
	use Illuminate\ Database\ Eloquent\ Model;
	use Illuminate\ Support\ Facades\ Store;
	use Illuminate\ Support\ Facades\ Storage;
	use Illuminate\Support\Facades\Mail;
	use Carbon\Carbon;
	use App\ Product;
	use App\ Orderline;
	use App\ Customer;
	use App\ Unit;
	use App\ Contact;
	use App\ Business;
	use App\ Driver;
	use App\ Vehicle;
	use App\ User;
	use App\ DriverLog;
	use View;
	use File;
	use App\Mail\DriverlogUpdates;
	use Dompdf\Dompdf;
	use Dompdf\Options;
	use Illuminate\Support\Facades\Redirect;
	use Illuminate\Routing\Route;
	use Spatie\Permission\Models\Role;
	use Spatie\Permission\Models\Permission;

	class FileController extends Controller
	{
		public function __construct()
		{
			$this->middleware('auth');
		}

		public function mailtest(Request $request)
		{
			Mail::to('alfred.laggner@gmail.com')->send(new DriverlogUpdates());
			return redirect()->route('make_manifest');
		}


		public function importExportExcelORCSV()
		{
			return view('file_import_export');
		}

		public function Start(Request $request)
		{

			$old_driver = '';
			$old_vehicle = '';
			$old_so = '';
			if ($request->session()->exists('driver')) {
				$old_driver = $request->session()->get('driver');
			}
			if ($request->session()->exists('vehicle')) {
				$old_vehicle = $request->session()->get('vehicle');
			}
			if ($request->session()->exists('so')) {
				$old_so = $request->session()->get('sales_orders');
			}
			return view('print_manifest', [
				'old_driver' => $old_driver,
				'old_vehicle' => $old_vehicle,
				'drivers' => User::where('license', '!=', NULL)->get(),
				'vehicles' => Vehicle::all(),
				'sales_orders' => $old_so,
			]);
		}

		public function importOrderLines($order_id)
		{
		//    dd($order_id);
			$odoo = new \Edujugon\Laradoo\Odoo();
			$odoo = $odoo->connect();
			$order_lines = $odoo
				->where('order_id','=', $order_id)
                ->fields(
                    'id',
                    'write_date',
                    'name',
                    'name_short',
                    'price_subtotal',
                    'move_ids',
                    'invoice_lines',
                    'untaxed_amount_to_invoice',
                    'untaxed_amount_invoiced',
                    'invoice_status',
                    'margin',
                    'qty_invoiced',
                    'qty_to_invoice',
                    'qty_delivered',
                    'product_uom_qty',
                    'price_unit',
                    'product_uom',
                    'create_date',
                    'order_partner_id',
                    'product_id',
                    'order_partner_id',
                    'list_price',
                    'order_id',
                    'purchase_price',
                    'salesman_id'
                )
                ->get('sale.order.line');
//dd($order_lines);
            \DB::table('manifest_orderlines')->delete();

            for ($i = 0; $i < count($order_lines); $i++) {
                $name_org = $order_lines[$i]['name'];
                $pos = strpos($name_org, ']');
                $code = '';
                if ($pos) {
                    $name = substr($name_org, $pos + 2);
                    $code = substr($name_org, 0, $pos + 2);
                } else {
                    $name = $name_org;
                }

                $revenue = $order_lines[$i]['price_unit'];
                if ($revenue > 0.01) {
                    $cost = $order_lines[$i]['purchase_price'];

                    $gross_profit = bcsub($revenue, $cost, 3);

                    if ($gross_profit != 0 and $revenue != 0 and $cost != 0) {
                        $margin = bcmul('100', bcdiv($gross_profit, $revenue, 3), 3);
                        // dd($margin);
                    } else {
                        $margin = 0;
                    };
                } else {
                    $margin = 0;
                }
            //   dd(substr($order_lines[$i]['order_id'][1], 2))  ;
                Orderline::updateOrCreate(
                    [
                        'ext_id' => $order_lines[$i]['id']
                    ],
                    [
                        'ext_id_shipping' => $order_lines[$i]['order_partner_id'][0],
                        'order_date' => $order_lines[$i]['create_date'],
                        'created_at' => $order_lines[$i]['create_date'],
                        'sales_person_id' => $order_lines[$i]['salesman_id'][0],
                        'product_id' => $order_lines[$i]['product_id'][0],
                     'order_id' => substr($order_lines[$i]['order_id'][1], 2),
                    //   'order_id' => $order_lines[$i]['order_id'][0],
                       'invoice_number' => $order_lines[$i]['order_id'][1],
                     //   'invoice_number' => "SO" . $order_lines[$i]['order_id'][0],
                        'customer_id' => $order_lines[$i]['order_partner_id'][0],
                        'name' => $name,
                        'code' => $code,
                        'quantity' => $order_lines[$i]['product_uom_qty'],
                        'cost' => $order_lines[$i]['purchase_price'],
                        'ext_id_unit' => $order_lines[$i]['product_uom'][1],
                        'unit_price' => $order_lines[$i]['price_unit'],
                        'margin' => $margin,
                        'amt_to_invoice' => $order_lines[$i]['untaxed_amount_to_invoice'] / 1.24,
                        'amt_invoiced' => $order_lines[$i]['untaxed_amount_invoiced'] / 1.24,
                        'price_subtotal' => $order_lines[$i]['price_subtotal'],
                        'invoice_status' => $order_lines[$i]['invoice_status'],
                        'odoo_margin' => $order_lines[$i]['margin'],
                        'qty_invoiced' => $order_lines[$i]['qty_invoiced'],
                        'qty_to_invoice' => $order_lines[$i]['qty_to_invoice'],
                        'qty_delivered' => $order_lines[$i]['qty_delivered'],
                    ]);

            }
			return;
		}


		public function makeManifests(Request $request)
		{
			$driver = User::find($request->get('driver'));
			$vehicle = Vehicle::find($request->get('vehicle'));
			//      $business = Business::first();

			$validatedData = $request->validate([
				'sale_orders' => 'required|numeric',
			]);


			session(['driver' => $request->get('user')]);
			session(['vehicle' => $request->get('vehicle')]);
			session(['sale_orders' => $request->get('sale_orders')]);

			$sale_order = explode(" ", $validatedData['sale_orders']);
			$sale_order_full = "SO" . $sale_order[0];
			$order_id = intval($sale_order[0]);
	//	dd($order_id);
            $sales_orders = SalesOrder::where('sales_order',"SO".$order_id)->first();
          //  dd($sales_orders);
			$this->importOrderLines($sales_orders->ext_id);
            $sales_lines = Orderline::get();
      //      dd($sales_lines);
			$delivery_date = today() ; // $this->getFromOdoo($id = $order_id);
	//		$this->driver_log($driver, $vehicle, $order_id, $delivery_date, $sales_lines, $sales_orders);

			$data = $this->printManifest($driver, $vehicle, $order_id, $sales_lines);
			$filename = '../storage/manifests/manifest_' . $order_id . '_' . date('d_m_Y_H_i') . '.pdf';
			\PDF::setOptions(['dpi' => 150, 'defaultMediaType' => 'screen', 'defaultFont' => 'sans-serif', 'enable_html5_parser' => true, 'orientation' => 'landscape']);
			return (\PDF::loadView('main_manifest', $data)->save($filename)->download('manifest.pdf'));

			return redirect()->route('make_manifest');
		}

		public function driver_log($driver, $vehicle, $order_id, $delivery_date, $sales_lines, $sales_orders)
		{
			$sale_invoice = $sales_lines->first();
			$so = $sales_orders->first();
			$sales_person_id = $sale_invoice->sales_person_id;
			$customer_id = $sale_invoice->ext_id_shipping;

			$driver_log = new DriverLog;
			$driver_log->vehicle_id = $vehicle->id;
			$driver_log->driver_id = $driver->id;
			$driver_log->saleinvoice_id = $order_id;
			$driver_log->salesperson_id = $sales_person_id;
			$driver_log->customer_id = $customer_id;
			$driver_log->delivery_date = $delivery_date;
			$driver_log->total = $so->amount_total;
			$driver_log->collected = $so->amount_total;
			$driver_log->order_date = $so->order_date;
			$driver_log->save();


		}


		public function messages()
		{
			return [
				'sale_orders.required' => 'Enter a valid sales order number',
			];
		}

		public function printManifest($driver, $vehicle, $sale_order_number, $sales_lines)
		{
			\PDF::setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'enable_html5_parser' => true, 'orientation' => 'landscape']);
			foreach ($sales_lines as $p) {
				//echo $p->code . "1<br>";
			}

			$productCount = $sales_lines->count();
			$test = env('app_testing');

			$pageTotal = 0;
			$printed = 0;
			$firstPageLines = 7;
			$attachedPageLines = 30;
			$pageLines = 0;
			$morePageCount = 0;

			$pageLines = 0;
			$morePageCount = 0;
			$isSamePageFirst = false;
			$isSamePageAttached = false;

			if ($productCount > $firstPageLines) {
				$pageTotal = 1;
			}
			$morePageLines = $productCount - $firstPageLines;
			if ($pageTotal or $morePageLines >= $attachedPageLines) {
				$pageTotal = ( int )($morePageLines / $attachedPageLines);
				if ($attachedPageLines % $morePageLines) {
					$pageTotal++;
				}
			}

			$totalLines = $productCount;
			$footerPageLines = 15;
			$leftover = 0;
			$onePageMore = 0;
			$firstPageTotal = $firstPageLines + $footerPageLines;
			$pageLines = $firstPageTotal;

			$attachedPageTotal = $attachedPageLines + $footerPageLines;
			$remainingLines = $totalLines - $firstPageTotal;
			$isAttachedPages = $remainingLines > 0 ? 1 : 0;
			$attachedPages = 1 + intval($remainingLines / $attachedPageTotal);
			if (!$isAttachedPages) {
				$isSamePageFirst = $totalLines <= $firstPageLines ? 'yes' : 'no';
			} else {
				$isSamePageAttached = $remainingLines <= $attachedPageLines ? 'yes' : 'no';
			}
			$data = [
				'test' => env('app_testing'),
				'all_products' => $sales_lines,
				'productCount' => $productCount,
				'invoice' => $sales_lines->first(),
				'business' => Business::first(),
				'driver' => $driver,
				'vehicle' => $vehicle,
				'pageCount' => 0,
				'pageTotal' => $pageTotal,
				'attachedPageLines' => $attachedPageLines,
				'pageAttached' => 0,
				'offset' => 0,
				'newoffset' => 0,
				'printed' => 0,
				'remainingLines' => $productCount,
				'footerLines' => 15,
				'firstPageTotal' => $firstPageTotal,
				'firstPageLines' => $firstPageLines,
				'attachedPageTotal' => $attachedPageTotal,
				'remainingLines' => $remainingLines,
				'isAttachedPages' => $isAttachedPages,
				'attachedPages' => $attachedPages,
				'totalLines' => $totalLines,
				'isSamePageFirst' => $isSamePageFirst,
				'isSamePageAttached' => $isSamePageAttached,
			];

			 		//	dd($data);
			return ($data);
		}

		public function getFromOdoo($id = '')
		{
			$odoo = new \Edujugon\Laradoo\Odoo();
			$odoo = $odoo->connect();
			$id = (int)$id;

			$picking = $odoo->where('sale_id', '=', $id)
				->limit(1)
				->fields(
					'scheduled_date'
				)
				->get('stock.picking');
			$delivery_date = substr($picking[0]['scheduled_date'], 0, 10);
			return $delivery_date;

		}


		public function importSalesOrderIntoDB($order, $odoo)
		{
			//      dd($order_lines);
			$arrlen = count($order);
			//	echo $arrlen;
			for ($i = 0; $i < $arrlen; $i++) {
				//  echo $i;
				//   $product_id = $order_lines[$i]['product_id'][0];
				//    echo $product_id . "<br>";         //   dd($product_id);
				//  $product = $odoo->where('id', '=', $product_id)->fields('code')->get('product.product');

				//   dd($product);
				//  dd("Not a valid sale order!");

				$order_date = ($order[0]['date_order'] == true) ? date_format(date_create($order[0]['date_order']), "Y-m-d") : NULL;
				$arr[] = [
					'order_date' => $order_date,
					'salesperson_id' => $order[0]['user_id'][0],
					'sales_order' => $order[0]['display_name'],
					//     'customer_id' => $order[0]['order_partner_id'],
					'sales_order_id' => substr($order[0]['display_name'], 2),
				];
			}
//dd($arr);

			if (!empty($arr)) {
				\
				DB::table('salesorders')->delete();
				\
				DB::table('salesorders')->insert($arr);
				//         Storage::delete('/public/sale.order.csv');
				return true;
			}
			return false();
		}


		public function importUsersIntoDB($users)
		{
			$arrlen = count($users);
//			echo $arrlen;
			for ($i = 0; $i < $arrlen; $i++) {
				if (!$users->isEmpty()) {
					$arr[] = [
						'sales_person_id' => $users[$i]['id'],
						'name' => $users[$i]['name'],
						'email' => $users[$i]['email'],
					];
				}
			}
			//		dd($arr);

			if (!empty($arr)) {
				\
				DB::table('salespersons')->delete();
				\
				DB::table('salespersons')->insert($arr);
				//         Storage::delete('/public/sale.order.csv');
				return true;
			}
			return false();
		}

		function strip_tags_content($text, $tags = '', $invert = FALSE)
		{

			preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
			$tags = array_unique($tags[1]);

			if (is_array($tags) AND count($tags) > 0) {
				if ($invert == FALSE) {
					return preg_replace('@<(?!(?:' . implode('|', $tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
				} else {
					return preg_replace('@<(' . implode('|', $tags) . ')\b.*?>.*?</\1>@si', '', $text);
				}
			} elseif ($invert == FALSE) {
				return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
			}
			return $text;
		}

		public

		function importUnitsIntoDB()
		{

			$path = storage_path('app/public/product.uom.csv');
			$data = \Excel::load($path)->get();
			if ($data->count()) {
				foreach ($data as $key => $value) {
					$arr[] = [
						'ext_id' => $value->id,
						'name' => $value->name,
					];

				}
				if (!empty($arr)) {
					\
					DB::table('units')->delete();
					\
					DB::table('units')->insert($arr);
					dd('Insert Units Records successfully.');
				}
			}
			dd('Request data does not have any files to import.');
		}

		public function importCustomersIntoDB($customer)
		{

			if (!$customer[0]['street2']) {
				$street2 = NULL;
			} else {
				$street2 = $customer[0]['street2'];
			}

			if ($customer) {
				$arr[] = [
					'ext_id' => $customer[0]['id'],
					'ext_id_contact' => $customer[0]['id'],
					'name' => preg_replace("/[^a-zA-Z0-9\s]/", " ", $customer[0]['display_name']),
					'street' => $customer[0]['street'],
					'street2' => $street2,
					'city' => $customer[0]['city'],
					'zip' => $customer[0]['zip'],
					'phone' => $customer[0]['phone'],
					'license' => substr($customer[0]['x_studio_field_mu5dT'], 0, 20),
				];
			}
			//    dd($arr);
			if (!empty($arr)) {
				/*            \DB::table('customers')->delete();*/
				\DB::table('customers')->insert($arr);
				//        dd('Insert Customers Records successfully.');
				return true;
			}
			//    dd('Request data does not have any files to import.');
			return false;
		}

		public function ximportCustomersIntoDB()
		{

			$path = storage_path('app/public/res.partner.csv');
			$data = \Excel::load($path)->get();
			if ($data->count()) {
				foreach ($data as $key => $value) {
					$arr[] = [
						'ext_id' => $value->id,
						'ext_id_contact' => $value->child_idsid,
						'name' => $value->name,
						'street' => $value->street,
						'street2' => $value->street2,
						'city' => $value->city,
						'zip' => $value->zip,
						'phone' => $value->phone,
						'license' => $value->x_studio_field_mu5dt
					];

				}
				if (!empty($arr)) {
					\
					DB::table('customers')->delete();
					\
					DB::table('customers')->insert($arr);
					//         dd('Insert Customers Records successfully.');
				}
			}
			dd('Request data does not have any files to import.');
		}

		public

		function importContactsIntoDB()
		{
			$path = storage_path('app/public/res.partner.csv');
			$data = \Excel::load($path)->get();
			if ($data->count()) {
				foreach ($data as $key => $value) {
					$arr[] = [
						'ext_id' => $value->id,
						'name' => $value->name,
						'phone' => $value->phone,
						'customer_id' => $value->parent_idid,
					];

				}
				if (!empty($arr)) {
					\
					DB::table('contacts')->delete();
					\
					DB::table('contacts')->insert($arr);
					dd('Insert Contacts Records successfully.');
				}
			}
			dd('Request data does not have any files to import.');
		}

		public

		function importProductsIntoDB()
		{

			$path = storage_path('app/public/product.template.csv');
			$data = \Excel::load($path)->get();
			if ($data->count()) {
				foreach ($data as $key => $value) {
					$arr[] = [
						'ext_id' => $value->id,
						'name' => $value->name,
						'description' => $value->name,
					];

				}
				if (!empty($arr)) {
					\
					DB::table('products')->delete();
					\
					DB::table('products')->insert($arr);
					dd('Inserted Product Records successfully.');
				}
			}
			dd('Request data does not have any files to import.');
		}


		public

		function downloadExcelFile($type)
		{
			$products = Product::get()->toArray();
			return \ Excel::create('expertphp_demo', function ($excel) use ($products) {
				$excel->sheet('sheet name', function ($sheet) use ($products) {
					$sheet->fromArray($products);
				});
			})->download($type);
		}

		public function additional()
		{
			return view('print_manifest_edit');
		}

	}
