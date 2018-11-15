<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Category;
use Illuminate\Console\Command;
use QL\Ext\PhantomJs;
use QL\QueryList;

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
            'crm-related=CRM相关' => [
                'sales=销售管理' => [
                    'crm=CRM',
                    'quote-management=报价管理',
                    'sales-acceleration=销售辅助',
                    'sales-intelligence=销售智能'
                ],
                'marketing=营销管理',
                'customer-service=客户服务' => [
                    'help-desk=桌面帮助',
                    'live-chat=在线客服',
                    'contact-center=呼叫中心',
                    'field-service-management=现场服务'
                ],
                'online-community-management=社交网络'
            ],
            'erp=ERP' => [
                'accounting-finance=财务管理' => [
                    'accounting=财务软件',
                    'billing=发票管理',
                    'budgeting-and-forecasting=预算管理',
                    'credit-and-collections=信贷风险管理',
                    'enterprise-payment=付款管理',
                    'travel-expense=费用管理',
                    'revenue-management=收入管理',
                    'treasury-management=司库管理'
                ],
                'asset-management-f3e79baa-6f93-4d40-b734-16e9b562fc14=资产管理'=>[
                    'enterprise-asset-management-eam=EAM企业资产管理'
                ],
                'erp-systems=ERP套件',
                'manufacturing-execution-system=MES制造执行系统',
                'project-portfolio-program-management=项目管理'
            ],
            'supply-chain-logistics=供应链管理' => [
                'supply-chain-suites=供应链管理套件',
                'demand-planning=需求计划',
                'inventory-management=库存管理',
                'sales-ops-planning=SOP销售运作计划',
                'supply-chain-planning=供应链计划'
            ],
            'office=办公' => [
                'authoring-and-publishing=编辑创作' => [
                    'design=设计',
                    'document-creation=文档',
                    'office-suites=办公套件'
                ],
                'email=邮件服务'
            ],
            'collaboration-productivity=协同',
            'development=开发',
            'cad-plm=CAD/PLM',
            'hr=HR',
            'b2b-marketplace-platforms=B2B平台',
            'e-commerce=电子商务',
            'content-management=内容管理',
            'digital-advertising=数字营销',
            'analytics=分析',
            'artificial-intelligence=AI',
            'it-infrastructure=IT基础设施',
            'it-management=IT治理',
            'security=安全',
            'hosting=托管',
            'vertical-industry=垂直行业解决'
        ];
        $software_c_list = [
            'professional-services-bd3033f2-37d6-456f-88bb-632ef5fc83f5=专业服务' => [
                'implementation-services=实施服务' => [
                    'microsoft-consulting=Microsoft产品咨询实施',
                    'sap-consulting=SAP产品咨询实施',
                    'oracle-consulting=Oracle产品咨询实施',
                    'salesforce-consulting=Salesforce产品咨询实施'
                ],
                'development-services=开发服务',
                'solution-consulting=解决方案咨询',
                'creative-services=创意服务'
            ],
            'business-services=商业服务',
            'marketing-services=市场营销服务',
            'staffing-services=人力资源服务',
            'translation-services=会务翻译服务',
            'cybersecurity-services=网络环境安全服务',
            'value-added-resellers-vars=增值服务',
            'other-services-a3556bb7-df48-4d51-af00-82ad0505f4c5=其他'
        ];

        foreach ($product_c_list as $key=>$product) {
            $this->addC($product_c,$key,$product);
        }

        foreach ($software_c_list as $key=>$software) {
            $this->addC($software_c,$key,$software,'enterprise_service_');
        }
    }

    protected function addC(Category $parent, $key, $products, $prefix = 'enterprise_product_') {
        if (is_array($products)) {
            $name = explode('=',$key);
            $parentP = Category::create([
                'parent_id' => $parent->id,
                'grade'     => 1,
                'name'      => $name[1],
                'icon'      => null,
                'slug'      => $prefix.$name[0],
                'type'      => 'enterprise_review',
                'sort'      => 0,
                'status'    => 1
            ]);
            foreach ($products as $key=>$product) {
                $this->addC($parentP,$key,$product,$prefix);
            }
        } else {
            $name = explode('=',$products);
            Category::create([
                'parent_id' => $parent->id,
                'grade'     => 0,
                'name'      => $name[1],
                'icon'      => null,
                'slug'      => $prefix.$name[0],
                'type'      => 'enterprise_review',
                'sort'      => 0,
                'status'    => 1
            ]);
        }

    }

}