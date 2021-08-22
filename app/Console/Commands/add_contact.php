<?php

	namespace App\Console\Commands;

	use Illuminate\Console\Command;

	class add_contact extends Command
	{
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'test:create';

		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Command description';

		/**
		 * Create a new command instance.
		 *
		 * @return void
		 */
		public function __construct()
		{
			parent::__construct();
		}

		/**
		 * Execute the console command.
		 *
		 * @return mixed
		 */
		public function handle()
		{
			$odoo = new \Edujugon\Laradoo\Odoo();
			$odoo = $odoo->connect();

			/*		$updated = $odoo->where('id', 1289)
						->update('res.partner',['name' => '11 Spot']);
			*/


			$id = $odoo->create('res.partner', [
				'name' => 'John Odoo',
				'phone' => '831-334-1553',
				'is_company' => true,
				'display_name' => 'John Odoo & Brothers',
				'street' => '226 Kelly Lane',
				'street2' => 'PO 12345',
				'city' => 'Santa Cruz',
				'zip' => '95060',
				'x_studio_field_mu5dT' => '85888-343434-2323',
				'x_studio_field_DN9mZ' => '34344=5656'
			]);

			dd($id);

		}
	}
