<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Category;
use Illuminate\Console\Command;

class ServiceCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:service:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化点评产品分类';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $product_c = Category::create([
            'parent_id' => 0,
            'grade'     => 1,
            'name'      => '产品',
            'icon'      => null,
            'slug'      => 'enterprise_product',
            'type'      => 'enterprise_review',
            'sort'      => 0,
            'status'    => 1
        ]);
        $software_c = Category::create([
            'parent_id' => 0,
            'grade'     => 1,
            'name'      => '服务',
            'icon'      => null,
            'slug'      => 'enterprise_service',
            'type'      => 'enterprise_review',
            'sort'      => 0,
            'status'    => 1
        ]);

        $product_c_list = [
            'CRM',
            'Analytics',
            'AI',
            'B2B Marketplace Platforms',
            'CAD/PLM',
            'Collaboration',
            'Content Management',
            'Development',
            'Digital Advertising',
            'E-Commerce',
            'ERP' => [
                'Accounting & Finance',
                'Asset Management',
                'ERP Suites',
                'Manufacturing Execution System'
            ],
            'HR',
            'IT Infrastructure',
            'IT Management',
            'Office',
            'Security',
            'Supply Chain' => [
                'Supply Chain Suites',
                'Demand Planning',
                'Inventory Management',
                'Supply Chain Analytics'
            ],
            'Vertical Industry'
        ];
        $software_c_list = [
            'Business Services',
            'Cybersecurity Services',
            'Marketing Services',
            'Professional Services' => [
                'Development Services Providers',
                'Solution Consulting Providers',
                'Implementation Services Providers' => [
                    'Microsoft Consulting Providers',
                    'Oracle Consulting Providers',
                    'Salesforce Consulting Providers',
                    'SAP Consulting Providers'
                ]
            ],
            'Staffing Services',
            'Translation Services',
            'Value-Added Resellers'
        ];

        foreach ($product_c_list as $key=>$product) {
            $this->addC($product_c,$key,$product);
        }

        foreach ($software_c_list as $key=>$software) {
            $this->addC($software_c,$key,$software);
        }
    }

    protected function addC(Category $parent, $key, $products) {
        if (is_array($products)) {
            $parentP = Category::create([
                'parent_id' => $parent->id,
                'grade'     => 1,
                'name'      => $key,
                'icon'      => null,
                'slug'      => 'enterprise_product_'.strtolower(str_replace(' ','_',$key)),
                'type'      => 'enterprise_review',
                'sort'      => 0,
                'status'    => 1
            ]);
            foreach ($products as $key=>$product) {
                $this->addC($parentP,$key,$product);
            }
        } else {
            Category::create([
                'parent_id' => $parent->id,
                'grade'     => 1,
                'name'      => $products,
                'icon'      => null,
                'slug'      => 'enterprise_product_'.strtolower(str_replace(' ','_',$products)),
                'type'      => 'enterprise_review',
                'sort'      => 0,
                'status'    => 1
            ]);
        }

    }

}