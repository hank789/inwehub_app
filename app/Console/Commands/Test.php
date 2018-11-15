<?php

namespace App\Console\Commands;

use App\Models\Attention;
use App\Models\Category;
use App\Models\Doing;
use App\Models\Question;
use App\Models\RecommendRead;
use App\Models\Scraper\BidInfo;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Services\Translate;
use App\Services\BosonNLPService;
use App\Services\MixpanelService;
use App\Services\QcloudService;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\WechatSpider;
use App\Traits\SubmitSubmission;
use GuzzleHttp\Exception\ConnectException;
use function GuzzleHttp\Psr7\parse_query;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PHPHtmlParser\Dom;
use QL\Ext\PhantomJs;
use QL\QueryList;
use Stichoza\GoogleTranslate\TranslateClient;


class Test extends Command
{
    use SubmitSubmission;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $keys = RateLimiter::instance()->hGetAll('tag_pending_translate');
        foreach ($keys as $id=>$v) {
            $tag = Tag::find($id);
            $tag->summary = Translate::instance()->translate($tag->description);
            $tag->save();
            RateLimiter::instance()->hDel('tag_pending_translate',$id);
        }
        return;
        $s = [
            'CRM & Related',
            'Sales Software',
            'CRM Software',
            'CRM All-in-One Software',
            'AI Sales Assistant Software',
            'Auto Dialer Software',
            'Contract Management Software',
            'E-Signature Software',
            'Field Sales Software',
            'Other Sales Software',
            'Partner Management Software',
            'Quote Management Software',
            'CPQ Software',
            'Pricing Software',
            'Proposal Software',
            'Visual Configuration Software',
            'Sales Acceleration Software',
            'Email Tracking Software',
            'Outbound Call Tracking Software',
            'Sales Coaching Software',
            'Sales Enablement Software',
            'Sales Engagement Software',
            'Salesforce CRM Document Generation Software',
            'Sales Performance Management Software',
            'Sales Training and Onboarding Software',
            'Sales Analytics Software',
            'Sales Gamification Software',
            'Sales Intelligence Software',
            'Marketing Software',
            'Account-Based Marketing Software',
            'Account-Based Execution Software',
            'Account-Based Reporting Software',
            'Marketing Account Intelligence Software',
            'Marketing Account Management Software',
            'Attribution Software',
            'Brand Data Management Software',
            'Content Marketing Software',
            'Content Creation Software',
            'Content Distribution Software',
            'Content Experience Software',
            'Conversational Marketing Software',
            'Conversion Rate Optimization Software',
            'A/B Testing Software',
            'Heat Maps Software',
            'Landing Page Builders',
            'Other Conversion Rate Optimization Software',
            'Session Replay Software',
            'Customer Data Platform (CDP) Software',
            'Customer Journey Analytics Software',
            'Demand Generation Software',
            'Brand Advocacy Software',
            'Customer Advocacy Software',
            'Employee Advocacy Software',
            'Gamification Software',
            'Lead Generation Software',
            'Lead Capture Software',
            'Lead Intelligence Software',
            'Lead Mining Software',
            'Lead Scoring Software',
            'Other Lead Generation Software',
            'Visitor Identification Software',
            'Loyalty Management Software',
            'Digital Analytics Software',
            'Digital Signage Software',
            'Email Deliverability Software',
            'Email Marketing Software',
            'Email Signature Software',
            'Event Management Software',
            'Audience Response Software',
            'Conference Intelligence Software',
            'Event Management Platforms',
            'Event Planning Software',
            'Event Registration & Ticketing Software',
            'Lead Retrieval Software',
            'Mobile Event Apps',
            'Other Event Management Software',
            'Venue Management Software',
            'Inbound Call Tracking Software',
            'Location Based Marketing Software',
            'Marketing Analytics Software',
            'Marketing Automation Software',
            'Marketing Resource Management Software',
            'Market Intelligence Software',
            'Mobile Marketing Software',
            'Multi-level Marketing (MLM) Software',
            'Online Reputation Management Software',
            'Other Marketing Software',
            'Print Fulfillment Software',
            'Public Relations (PR) Software',
            'Media and Influencer Targeting Software',
            'Media Monitoring Software',
            'Other Public Relations Software',
            'PR Analytics Software',
            'Press Release Distribution Software',
            'Search Marketing Software',
            'Local SEO Software',
            'SEO Software',
            'Social Media Marketing Software',
            'Influencer Marketing Software',
            'Other Social Media Software',
            'Social Media Analytics Software',
            'Social Media Management Software',
            'Social Media Monitoring Software',
            'Social Media Suites',
            'Sweepstakes Software',
            'Tag Management Software',
            'User Research Software',
            'Customer Service Software',
            'Help Desk Software',
            'Live Chat Software',
            'Customer Self-Service Software',
            'Social Customer Service Software',
            'Contact Center Software',
            'Contact Center Infrastructure Software',
            'Contact Center Workforce Software',
            'Telecom Services for Call Centers Software',
            'Customer Success Software',
            'Employee Monitoring Software',
            'Enterprise Feedback Management Software',
            'Field Service Management Software',
            'Other Customer Service Software',
            'Proactive Notification Software',
            'Online Community Management Software',
            'Analytics',
            'Business Intelligence Software',
            'Business Intelligence Platforms Software',
            'Data Visualization Software',
            'Embedded Business Intelligence Software',
            'Location Intelligence Software',
            'Self-Service Business Intelligence Software',
            'Data Virtualization Software',
            'Enterprise Drone Analytics Software',
            'Enterprise Search Software',
            'Enterprise Semantic Search Software',
            'Other Analytics Software',
            'Predictive Analytics Software',
            'Text Analysis Software',
            'Artificial Intelligence',
            'AI Platforms Software',
            'Conversational Intelligence Software',
            'Bot Platforms Software',
            'Chatbots Software',
            'Natural Language Generation (NLG) Software',
            'Natural Language Processing (NLP) Software',
            'Deep Learning Software',
            'Artificial Neural Network Software',
            'Image Recognition Software',
            'Voice Recognition Software',
            'Machine Learning Software',
            'AR/VR',
            'Augmented Reality Software',
            'AR CAD Software',
            'AR Visualization Software',
            'AR Content Management Systems',
            'AR Development Software',
            'AR SDK Software',
            'AR WYSIWYG Editor Software',
            'AR Game Engine Software',
            'AR Training Simulator Software',
            'Industrial AR Platforms',
            'Virtual Reality Software',
            'VR CAD Software',
            'VR Visualization Software',
            'VR Content Management Systems',
            'VR Development Software',
            'VR Marketplace Software',
            'VR SDK Software',
            'VR Game Engine Software',
            'VR Social Platforms',
            'VR Training Simulator Software',
            'B2B Marketplace Platforms',
            'Merchant Marketing Software',
            'On-Demand Delivery Software',
            'Grocery Delivery Software',
            'On-Demand Catering Software',
            'Restaurant Delivery/Takeout Software',
            'On-Demand Wellness Software',
            'Ride Sharing Software',
            'CAD & PLM',
            'CAD Software',
            'Building Design and Building Information Modeling (BIM) Software',
            'Civil Engineering Design Software',
            'General-Purpose CAD Software',
            'Other CAD Software',
            'Product and Machine Design Software',
            'Sketching Software',
            'Computer-Aided Engineering (CAE) Software',
            'Computer-Aided Manufacturing Software',
            'GIS Software',
            'PLM Software',
            'Quality Management (QMS)',
            'Collaboration & Productivity',
            'Team Collaboration Software',
            'Other Collaboration Software',
            'Audio Conferencing Software',
            'Board Management Software',
            'Employee Intranet Software',
            'G Suite Marketplace Software',
            'G Suite Business Tools',
            'G Suite Administration Software',
            'G Suite ERP Software',
            'G Suite for Finance Software',
            'G Suite for HR Software',
            'G Suite for Marketing Software',
            'G Suite for Sales Software',
            'G Suite Communication Tools',
            'G Suite Education Software',
            'G Suite Academic Software',
            'G Suite Productivity Tools',
            'G Suite Creative Tools',
            'G Suite Development Tools',
            'G Suite Office Tools',
            'G Suite Project Management Software',
            'G Suite Utilities Software',
            'Idea Management Software',
            'Internal Communications Software',
            'Note-Taking Management Software',
            'Productivity Bots Software',
            'Social Networks Software',
            'Structured Collaboration Software',
            'Video Conferencing Software',
            'Video Conferencing (Personal) Software',
            'VoIP Providers',
            'Webinar Software',
            'Content Management',
            'Business Content Management Software',
            'CMS Tools',
            'Content Analytics Software',
            'Desktop Search Software',
            'Digital Asset Management Software',
            'Digital Experience Platforms (DXP) Software',
            'Document Capture Software',
            'Enterprise Content Management (ECM) Software',
            'File Migration Software',
            'File Storage and Sharing Software',
            'Headless CMS Software',
            'Knowledge Management Software',
            'Localization Software',
            'Computer-Assisted Translation Software',
            'Machine Translation Software',
            'Translation Management Software',
            'Mobile Forms Automation Software',
            'Online Form Builder Software',
            'Online Proofing Software',
            'User-Generated Content Software',
            'Video CMS Software',
            'Virtual Data Room Software',
            'Web Content Management Software',
            'Website Accessibility Software',
            'Website Builder Software',
            'Development',
            'Cloud Platform as a Service (PaaS) Software',
            'Continuous Delivery Software',
            'Continuous Deployment Software',
            'Continuous Integration Software',
            'Build Automation Software',
            'Configuration Management Software',
            'Other Continuous Delivery Software',
            'Integrated Development Environment (IDE) Software',
            'Bug Tracking Software',
            'Help Authoring Tool (HAT) Software',
            'Other Development Software',
            'API Management Software',
            'API Marketplace Software',
            'Application Development Software',
            'ALM Suites Software',
            'Mobile Development Software',
            'Mobile Development Platforms Software',
            'Drag and Drop App Builder Software',
            'Mobile Development Frameworks Software',
            'Mobile Backend-as-a-Service (mBaaS) Software',
            'Mobile App Debugging Software',
            'Mobile App Testing Software',
            'Cloud Communication Platforms Software',
            'Mobile Analytics Software',
            'Mobile App Analytics Software',
            'Mobile Crash Reporting Software',
            'Mobile App Optimization Software',
            'Other Mobile Development Software',
            'Rapid Application Development (RAD) Software',
            'Low-Code Development Platforms Software',
            'No-Code Development Platforms Software',
            'Application Release Orchestration Software',
            'Editor Software',
            'Text Editor',
            'WYSIWYG Editors Software',
            'Game Development Software',
            'Audio Engine Software',
            'Game Engine Software',
            'Gaming Tools',
            'Physics Engine Software',
            'Portals Software',
            'Product Management Software',
            'Python Package Software',
            'Source Code Management Software',
            'Version Control Systems',
            'Peer Code Review Software',
            'Static Code Analysis Software',
            'Version Control Clients Software',
            'Version Control Hosting Software',
            'Test Management Software',
            'Software Testing Tool',
            'Test Automation Software',
            'Web Frameworks Software',
            'Other Web Frameworks Software',
            'Java Web Frameworks Software',
            'PHP Web Frameworks Software',
            'Python Web Frameworks Software',
            'Digital Advertising',
            'Ad Network Software',
            'Advertiser Campaign Management Software',
            'Creative Management Platforms',
            'Cross-Channel Advertising Software',
            'Demand Side Platform (DSP)',
            'Display Advertising Software',
            'Mobile Advertising Software',
            'Native Advertising Software',
            'Search Advertising Software',
            'Social Media Advertising Software',
            'Video Advertising Software',
            'Data Management Platform (DMP) Software',
            'Identity Resolution Software',
            'Other Digital Advertising Software',
            'Publisher Ad Management Software',
            'App Monetization Software',
            'Other Publisher Management Software',
            'Publisher Ad Server Software',
            'Supply Side Platform (SSP) Software',
            'E-Commerce',
            'Catalog Management Software',
            'E-Commerce Platforms Software',
            'E-Commerce Tools',
            'E-Merchandising Software',
            'Fraud Protection Software',
            'Multichannel Retail Software',
            'Payment Gateway Software',
            'Product Reviews Software',
            'Subscription Management Software',
            'E-Commerce Personalization Software',
            'Product Information Management (PIM) Software',
            'Shopping Cart Software',
            'Subscription Analytics Software',
            'Subscription Billing Software',
            'Subscription Revenue Management Software',
            'ERP',
            'Accounting & Finance Software',
            'Procurement Software',
            'Purchasing Software',
            'Strategic Sourcing Software',
            'Vendor Management Software',
            'Order Management Software',
            'Accounting Software',
            'AP Automation Software',
            'AR Automation Software',
            'Billing Software',
            'Budgeting and Forecasting Software',
            'Corporate Performance Management (CPM) Software',
            'Corporate Tax Software',
            'Credit and Collections Software',
            'Enterprise Payment Software',
            'Financial Analysis Software',
            'Financial Close Software',
            'Other Finance & Admin. Software',
            'Payroll Software',
            'POS Software',
            'Restaurant POS System',
            'Retail POS System',
            'Revenue Management Software',
            'Sales Tax Compliance Software',
            'Small-Business Accounting Software',
            'Travel & Expense Software',
            'Travel Management Software',
            'Expense Management Software',
            'Invoice Management Software',
            'Mileage Tracking Software',
            'Treasury Management Software',
            'Asset Management Software',
            'Asset Leasing Software',
            'CMMS Software',
            'Enterprise Asset Management (EAM) Software',
            'Discrete ERP Software',
            'Distribution ERP Software',
            'Document Generation Software',
            'Environmental Health and Safety Software',
            'ERP Systems',
            'ETO ERP Software',
            'Manufacturing Execution System Software',
            'Mixed Mode ERP Software',
            'Process ERP Software',
            'Professional Services Automation Software',
            'Project-Based ERP Software',
            'Project, Portfolio & Program Management Software',
            'Project and Portfolio Management Software',
            'Project Management Software',
            'Strategic Planning Software',
            'Task Management Software',
            'Strategy and Innovation Roadmapping Tools Software',
            'Tools for ERP Software',
            'Governance, Risk & Compliance',
            'Anti Money Laundering Software',
            'Audit Management Software',
            'Business Continuity Management Software',
            'Data Privacy Software',
            'Disclosure Management Software',
            'Ethics and Compliance Learning Software',
            'GRC Platforms Software',
            'IT Risk Management Software',
            'Operational Risk Management Software',
            'Policy Management Software',
            'Regulatory Change Management Software',
            'Third Party & Supplier Risk Management Software',
            'Hosting',
            'Content Delivery Network (CDN) Software',
            'Domain Registration Providers',
            'Enterprise Content Delivery Network (eCDN) Software',
            'Managed DNS Providers Software',
            'Managed Hosting Providers',
            'Managed Services Providers',
            'Managed Workplace Services (MWS) Software',
            'Other Hosting Services Providers',
            'Virtual Private Servers (VPS) Providers',
            'Web Hosting Providers',
            'HR',
            'HR Management Suites Software',
            'Talent Management Software',
            'Recruiting Software',
            'Talent Acquisition Suites Software',
            'Recruitment Marketing Platforms',
            'Applicant Tracking System (ATS) Software',
            'Onboarding Software',
            'Video Interviewing Software',
            'Pre-Employment Screening Software',
            'Pre-Employment Testing Software',
            'Soft Skills Assessment Software',
            'Technical Skills Screening Software',
            'Reference Check Software',
            'Background Check Software',
            'Other Recruiting Software',
            'Job Boards Software',
            'Recruiting Automation Software',
            'Training eLearning Software',
            'Corporate LMS Software',
            'Course Authoring Software',
            'eLearning Content Software',
            'Learning Experience Platform (LEP) Software',
            'Mentoring Software',
            'Training Management System Software',
            'Performance Management System',
            'Employee Engagement Software',
            'Compensation Management Software',
            'Career Management Software',
            'Employee Recognition Software',
            'Relocation Management Software',
            'Core HR Software',
            'Benefits Administration Software',
            'Workforce Management Software',
            'Time Tracking Software',
            'Sales Compensation Software',
            'Freelance Platforms',
            'Staffing Software',
            'Other HR Software',
            'Corporate Wellness Software',
            'Employee Scheduling Software',
            'Financial Wellness Software',
            'Freelance Management Platforms',
            'HR Analytics Software',
            'HR Case Management Software',
            'HR Service Delivery Software',
            'Multi-Country Payroll Software',
            'PEO Software Software',
            'Time & Attendance Software',
            'IT Infrastructure',
            'Infrastructure as a Service (IaaS) Providers',
            'Application Performance Monitoring (APM) Software',
            'Application Server Software',
            'Blockchain Software',
            'Blockchain Platforms Software',
            'Cryptocurrency Software',
            'Cryptocurrency Exchanges',
            'Cryptocurrency Mining Software',
            'Cryptocurrency Payment Apps',
            'Cryptocurrency Wallets',
            'Containerization Software',
            'Container Engine Software',
            'Container Management Software',
            'Container Monitoring Software',
            'Container Networking Software',
            'Container Orchestration Software',
            'Container Registry Software',
            'Runtime Software',
            'Service Discovery Software',
            'Database Software',
            'Relational Databases Software',
            'NoSQL Databases Software',
            'Document Databases Software',
            'XML Databases Software',
            'Graph Databases Software',
            'RDF Databases Software',
            'Key-Value Stores',
            'Object-Oriented Databases Software',
            'Other Non-Relational Databases Software',
            'Non-Native Database Management Systems Software',
            'Big Data Software',
            'Big Data Analytics Software',
            'Big Data Processing and Distribution Software',
            'Event Stream Processing Software',
            'Database as a Service (DBaaS) Provider',
            'Desktop Database Software',
            'Structured Data Archiving and Application Retirement Software',
            'Data Center Infrastructure Management (DCIM) Software',
            'Data Center Networking Software',
            'Data Integration Software',
            'Cloud Data Integration Software',
            'Big Data Integration Platform',
            'Cloud Migration Software',
            'E-Commerce Data Integration Software',
            'Enterprise Service Bus (ESB) Software',
            'ETL Tools',
            'iPaaS Software',
            'Other Cloud Integration Software',
            'Stream Analytics Software',
            'Electronic Data Interchange (EDI) Software',
            'Integration Brokerage Software',
            'Managed File Transfer (MFT) Software',
            'On-Premise Data Integration Software',
            'Data Preparation Software',
            'Data Quality Software',
            'Data Warehouse Software',
            'IoT Management Software',
            'Load Balancing Software',
            'Log Analysis Software',
            'Machine Learning Data Catalog Software',
            'Master Data Management (MDM) Software',
            'Network Management Software',
            'Network Monitoring Software',
            'Operating System',
            'Other IT Infrastructure Software',
            'Remote Desktop Software',
            'Remote Support Software',
            'Server Virtualization Software',
            'Storage Management Software',
            'Block Storage Software',
            'Cloud File Storage Software',
            'Cold Storage Software',
            'Hybrid Cloud Storage Software',
            'Object Storage Software',
            'Transactional Email Software',
            'Virtual Desktop Infrastructure (VDI) Software',
            'Virtual Private Cloud (VPC) Software',
            'WAN Edge Infrastructure Software',
            'WAN Optimization Software',
            'Web Accelerator Software',
            'Web Client Accelerator Software',
            'Web Server Accelerator Software',
            'IT Management',
            'Cloud Cost Management Software',
            'Data Recovery Software',
            'Disaster Recovery as a Service (DRaaS) Software',
            'Backup Software',
            'File Recovery Software',
            'Online Backup Software',
            'SaaS Backup Software',
            'Server Backup Software',
            'Digital Governance Software',
            'Enterprise Architecture Software',
            'Enterprise Information Archiving Software',
            'Enterprise IT Management Suites Software',
            'Enterprise Mobility Management Software',
            'Incident Management Software',
            'Information Stewardship Applications Software',
            'IT Alerting Software',
            'IT Asset Management Software',
            'IT Portfolio Analysis Software',
            'IT Process Automation Software',
            'IT Resilience Orchestration Automation (ITRO) Software',
            'IT Service Management Tools Software',
            'Mobile Application Management Software',
            'Mobile Device Management (MDM) Software',
            'Network Automation Software',
            'Other IT Management Software',
            'Process Automation Software',
            'Business Process Management Software',
            'Process Mining Software',
            'Robotic Process Automation (RPA) Software',
            'SaaS Operations Management Software',
            'SaaS Spend Management Software',
            'SD-WAN Software',
            'Service Desk Software',
            'Software Asset Management Software',
            'Telecom Expense Management (TEM) Services Software',
            'Terminal Emulator Software',
            'Virtual IT Labs Software',
            'Workload Automation Software',
            'Office',
            'Authoring and Publishing Software',
            'Virtual Tour Software',
            'Audio Editing Software',
            'Design Software',
            '3D Design Software',
            '3D Modeling Software',
            '3D Painting Software',
            '3D Rendering Software',
            'Diagramming Software',
            'Display Ad Design Software',
            'Graphic Design Software',
            'Desktop Publishing Software',
            'Drawing Software',
            'Vector Graphics Software',
            'Other Design Software',
            'Photography Software',
            'Photo Editing Software',
            'Photo Management Software',
            'Software Design Software',
            'Prototyping Software',
            'Web Design Software',
            'Wireframing Software',
            'Document Creation Software',
            'Office Suites Software',
            'Video Software',
            'Animation Software',
            'Other Video Software',
            'Video Editing Software',
            'Video Effects Software',
            'Video Hosting Software',
            'Browser Software',
            'Calendar Software',
            'Email Software',
            'Email Verification Software',
            'Emergency Notification Software',
            'Fax Software',
            'Handwritten Notes Software',
            'Marketplace Apps Software',
            'Salesforce AppExchange Tools',
            'ServiceNow Store Apps',
            'Meeting Room Booking System Software',
            'Online Appointment Scheduling Software',
            'Org Chart Software',
            'Other Email Software',
            'Other Office Software',
            'Package Tracking Software',
            'Presentation Software',
            'Print Management Software',
            'Screen and Video Capture Software',
            'Spreadsheets Software',
            'Survey Software',
            'Visitor Management Software',
            'Security',
            'Risk Assessment Software',
            'Incident Response Software',
            'Security Information and Event Management (SIEM) Software',
            'Threat Intelligence Software',
            'Vulnerability Management Software',
            'Patch Management Software',
            'Runtime Application Self-Protection (RASP) Software',
            'Security Risk Analysis Software',
            'Vulnerability Scanner Software',
            'System Security Software',
            'Application Security Software',
            'Dynamic Application Security Testing (DAST) Software',
            'Penetration Testing Software',
            'Static Application Security Testing (SAST) Software',
            'Web Application Firewall (WAF) Software',
            'Data Security Software',
            'Mobile Data Security Software',
            'Network Security Software',
            'Endpoint Protection Software',
            'Antivirus Software',
            'Endpoint Detection & Response (EDR) Software',
            'Endpoint Management Software',
            'Endpoint Protection Suites',
            'Firewall Software',
            'Web Security Software',
            'Cloud Security Software',
            'Secure Email Gateway Software',
            'Secure Web Gateway Software',
            'Browser Isolation Software',
            'Cloud Workload Protection Platforms Software',
            'Website Security Software',
            'Application Shielding Software',
            'Confidentiality Software',
            'Encryption Software',
            'Virtual Private Network (VPN) Software',
            'Data Masking Software',
            'DDoS Protection Software',
            'DMARC Software',
            'Fraud Detection Software',
            'Identity Management Software',
            'Multi-Factor Authentication Software',
            'Identity and Access Management Software',
            'Customer Identity and Access Management Software',
            'Privileged Access Management Software',
            'Password Manager Software',
            'Risk-based Authentication Software',
            'Single Sign On (SSO) Software',
            'User Provisioning/Governance Software',
            'IoT Security Software',
            'Network Access Control Software',
            'Network Sandboxing Software',
            'Other IT Security Software',
            'Security Awareness Training Software',
            'Unified Threat Management (UTM) Software',
            'Supply Chain & Logistics',
            'Supply Chain Suites Software',
            'Demand Planning Software',
            'Distribution Software',
            'Fleet Management Software',
            'Route Planning Software',
            'Transportation Management Software',
            'Inventory Management Software',
            'Warehouse Management Software',
            'Barcode Software',
            'Inventory Control Software',
            'Other Supply & Logistics Software',
            'Shipping Software',
            'Multicarrier Parcel Management Solutions Software',
            'Sales & Ops Planning Software',
            'Supply Chain Analytics Technology Software',
            'Supply Chain Business Networks Software',
            'Supply Chain Cost-To-Serve Analytics Software',
            'Supply Chain Planning Software',
            'Yard Management Software',
            'Vertical Industry',
            'Agriculture Software',
            'Crop Management Software',
            'Farm Management Software',
            'Livestock Management Software',
            'Other Agriculture Software',
            'Precision Agriculture Software',
            'Smart Irrigation Software',
            'UAV Software',
            'Alumni Management Software',
            'Apparel Software',
            'Architecture Software',
            'Association Management Software',
            'Auction Software',
            'Automotive Software',
            'Auto Repair Software',
            'Car Rental Software',
            'Other Automotive Software',
            'Towing Software',
            'Aviation Software',
            'Aviation Authoring Software',
            'Aviation Compliance Monitoring Software',
            'Aviation Document Distribution Software',
            'Aviation MRO Software',
            'Flight Operation Manual Authoring Software',
            'Awards Management Software',
            'Camp Management Software',
            'Cemetery Software',
            'Child Care Software',
            'Other Child Care Software',
            'Church Management Software',
            'Church Presentation Software',
            'Cleaning Services Software',
            'Clinical Trial Management Software',
            'Communications and Media Software',
            'Integrated Revenue and Customer Management (IRCM) for CSPs Software',
            'Construction Software',
            'Bid Management Software',
            'Construction Accounting Software',
            'Construction Estimating Software',
            'Construction Management Software',
            'Construction Suites Software',
            'HVAC Software',
            'Other Construction Software',
            'Courier Software',
            'Dental Software',
            'Dental Imaging Software',
            'Dental Practice Management Software',
            'Other Dental Software',
            'Dry Cleaning Software',
            'Education Software',
            'Learning Management System (LMS) Software',
            'Student Information Systems (SIS) Software',
            'Higher Education Student Information Systems Software',
            'K-12 Student Information Systems Software',
            'Classroom Management Software',
            'Assessment Software',
            'Study Tools',
            'Language Learning Software',
            'Online Course Providers',
            'School Resource Management Software',
            'Special Education Software',
            'Reference Management Software',
            'Other Education Software',
            'Admissions and Enrollment Management Software',
            'Library Management Software',
            'Online Learning Platform',
            'Technical Skills Development Software',
            'Tutoring Software',
            'Virtual Classroom Software',
            'Equipment Rental Software',
            'Financial Services Software',
            'Banking Software',
            'Brokerage Trading Platforms Software',
            'Digital Banking Platforms Software',
            'Financial Data APIs',
            'Financial Research Software',
            'Insurance Software',
            'Insurance Agency Management Software',
            'Insurance Billing Software',
            'Insurance Claims Management Software',
            'Insurance Compliance Software',
            'Insurance Policy Administration Systems Software',
            'Insurance Suites Software',
            'Life Insurance Policy Administration Systems Software',
            'Other Insurance Software',
            'Property & Casualty Policy Administration Systems Software',
            'Underwriting & Rating Software',
            'Investment Portfolio Management Software',
            'Loan Software',
            'Loan Origination Software',
            'Loan Servicing Software',
            'Mortgage CRM Software',
            'Other Finance & Insurance Software',
            'Fitness Software',
            'Gym Management Software',
            'Other Fitness Software',
            'Other Gym Software',
            'Personal Training Software',
            'Sports League Management Software',
            'Food Software',
            'Food Service Distribution Software',
            'Food Service Management Software',
            'Food Traceability Software',
            'Other Food Software',
            'Forestry Software',
            'Funeral Home Software',
            'Health Care Software',
            'Assisted Living Software',
            'Chiropractic Software',
            'Clinical Communication and Collaboration Software',
            'CRM in Pharma and Biotech Software',
            'EHR Software',
            'Health Care Credentialing Software',
            'Healthcare Payer Care Management Workflow Applications Software',
            'Healthcare Payers\' Core Administrative Processing Solutions Software',
'Home Health Care Software',
'Interactive Patient Care Systems (IPC) Software',
'LIMS Software',
'Medical Billing Software',
'Medical Practice Management Software',
'Medical Scheduling Software',
'Mental Health Software',
'Optometry Software',
'Other Health Care Software',
'Pharmacy Software',
'Physical Therapy Software',
'Population Health Management Software',
'Practice Management Software',
'Private Duty Home Care Software',
'Radiology Software',
'Value-Based Performance Management Analytics Software',
'Vendor-Neutral Archives (VNA) Software',
'Home Furnishing Software',
'Hospitality Software',
'Hotel Software',
'Catering Software',
'Channel Management Software',
'Concierge Software',
'Guest Messaging Software',
'Hotel Management Software',
'Other Hospitality Software',
'Reservation Software',
'Hotel Reservations Software',
'Other Reservations Software',
'Restaurant Reservations Software',
'Restaurant Software',
'Restaurant Business Intelligence & Analytics Software',
'Restaurant Inventory Management & Purchasing Software',
'Restaurant Management Software',
'Kennel Software',
'Landscape Design Software',
'Legal Software',
'Court Management Software',
'eDiscovery Software',
'E-Notary Software',
'Intellectual Property Management Software',
'Legal Billing Software',
'Legal Case Management Software',
'Legal Operations Software',
'Legal Practice Management Software',
'Legal Research Software',
'Other Legal Software',
'Social Discovery Software',
'Marine Software',
'Harbor Operations Software',
'Marina Management Software',
'Other Marine Software',
'Medical Software',
'Mining Software',
'MPM and MbM Technology for Process Manufacturing Software',
'Museum Software',
'Collections Management Software',
'Gallery Management Software',
'Museum Management Software',
'Nonprofit Software',
'Admission-Based Nonprofit Software',
'Donor Management Software',
'Donor Prospect Research Software',
'Fundraising Software',
'Grant Management Software',
'Nonprofit Accounting Software',
'Nonprofit CRM Software',
'Nonprofit Payment Gateway Software',
'Other Nonprofit Software',
'Volunteer Management Software',
'Oil and Gas Software',
'Exploration Software',
'Geology and Seismic Software',
'Oil and Gas Asset Management Software',
'Oil and Gas Back Office Software',
'Oil and Gas Engineering Software',
'Oil and Gas Project Management Software',
'Oil and Gas Simulation and Modeling Software',
'Oil and Gas Training Software',
'Oil Production Software',
'Other Oil and Gas Software',
'Other Vertical Industry Software',
'Parking Management Software',
'Parks and Recreation Software',
'Pest Control Software',
'Physical Security Software',
'Political Campaign Software',
'Public Safety Software',
'Emergency Management Software',
'Emergency Medical Services Software',
'Fire Department Software',
'Jail Management Software',
'Other Public Safety Software',
'Police Software',
'Public Sector Software',
'US Federal Government Software',
'US State & Local Government Software',
'Public Works Software',
'Real Estate Software',
'Facility Management Software',
'Real Estate Asset Management Software',
'Real Estate Portfolio Management Software',
'Brokerage Management Software',
'IWMS Software',
'Lease Administration Software',
'Property Management Software',
'Real Estate CRM Software',
'Multiple Listing Service (MLS) Listing Software',
'Real Estate Marketing Software',
'Real Estate Activities Management Software',
'Community Association Management Software',
'Lease Accounting Software',
'Multifamily Software',
'Other Real Estate Software',
'Real Estate License School Software',
'Real Estate Virtual Tour Software',
'Vacation Rental Software',
'Retail Software',
'Automotive Retail Software',
'Car Dealer Software',
'Bakery Software',
'Florist Software',
'In-Store Logistics Systems',
'Jewelry Store Management Software',
'Other Retail Software',
'Retail Assortment Management Applications Software',
'Retail Distributed Order Management Systems Software',
'Retail Management System',
'Retail Task Management Software',
'Trade Promotion Management Software',
'Spa Management Software',
'Sustainability Management Software',
'Transportation Software',
'Other Transportation Software',
'Public Transportation Software',
'Taxi & Limousine Software',
'Trucking Software',
'Travel Arrangement Software',
'Other Travel Arrangement Software',
'Tour Operator Software',
'Travel Agency Software',
'Utilities Software',
'Meter Data Management Software',
'Utilities’ Customer Information System (CIS) Software',
'Veterinary Software',
'Other Veterinary Software',
'Veterinary Practice Management Software',
'Waste Management Software',
'Winery Software',
'Vineyard Management Software',
'Winery Management Software',
            'Business Filing and Licensing Providers',
            'Business Finance Providers',
            '409A Valuations Providers',
            'Accounting Firms',
            'Bookkeeping Services Providers',
            'Finance and Accounting Business Process Outsourcing Service Providers',
            'Financial Consulting Providers',
            'Other Business Finance Providers',
            'Tax Services Providers',
            'HR Services Providers',
            'Benefits Administration Services Providers',
            'Health & Safety Providers',
            'HR Consulting Providers',
            'Other HR Services Providers',
            'Payroll Services Providers',
            'Relocation Management Services',
            'Training & Development Companies',
            'Legal Services Providers',
            'Corporate Law Firms',
            'Intellectual Property (IP) Law Firms',
            'Other Legal Services Providers',
            'Managed Print Services',
            'Management Consulting Providers',
            'Revenue Operations Services Providers',
            'Sales Consulting Providers',
            'Supply Chain Strategy and Operations Consulting Providers',
            'Cybersecurity Services',
            'Application Security Services Providers',
            'Cybersecurity Consulting Providers',
            'Data Security Services Providers',
            'Email Security Services Providers',
            'Endpoint Security Services Providers',
            'Incident Response Services Providers',
            'IT Compliance Services Providers',
            'Managed Security Services Providers',
            'Network Security Services Providers',
            'Other Security Services Providers',
            'Threat Intelligence Services Providers',
            'Vulnerability Assessment Services Providers',
            'Greentech',
            'Batteries',
            'Battery Storage Systems Providers',
            'Marketing Services',
            'Branding Agencies',
            'Inbound Marketing Services',
            'Content Marketing Agencies',
            'Search Engine Marketing (SEM) Agencies',
            'PPC Services Providers',
            'SEO Services Providers',
            'Social Media Marketing (SMM) Companies',
            'Lead Generation Services',
            'Marketing Analytics Services Providers',
            'Marketing Automation Consulting Providers',
            'Marketing Strategy Agencies',
            'Other Marketing Services Providers',
            'Outbound Marketing Services',
            'Advertising Agencies',
            'Digital Marketing Services',
            'Experiential Advertising Agencies',
            'Traditional Advertising Agencies',
            'Email Marketing Services Providers',
            'Mobile Marketing Companies',
            'PR Firms',
            'Other Services',
            'Advanced Distribution Management Systems Providers',
            'Coworking Spaces',
            'IT Outsourcing Services',
            'ITSM Tool Implementation, Consulting, and Managed Services Providers',
            'Managed Live Chat Providers',
            'Online Program Management in Higher Education Providers',
            'Other B2B Services Providers',
            'Rewards and Incentives Services',
            'Technology Research Services',
            'Third-Party Logistics Providers',
            'Professional Services',
            'Creative Services Providers',
            'Content Writing Services Providers',
            'Graphic Design Services Providers',
            'Other Creative Services Providers',
            'User Experience (UX) Design Services Providers',
            'Video Production Companies',
            'Website Design Companies',
            'Development Services Providers',
            'Mobile App Development Companies',
            'Android Developers',
            'Cross-Platform Developers',
            'Internet of Things (IoT) Developers',
            'iOS Developers',
            'Wearable App Development Companies',
            'Windows Developers',
            'Other Development Services Providers',
            'Testing and QA Providers',
            'Web Developers',
            'Drupal Development Companies',
            'E-Commerce Development Companies',
            'Java Development Providers',
            '.NET Developers',
            'PHP Developers',
            'Python and Django Developers',
            'Ruby on Rails Developers',
            'Sitecore Developers',
            'WordPress Developers',
            'Hadoop Operations Providers',
            'Implementation Services Providers',
            'Acumatica ERP Consulting Software',
            'Acumatica Consulting Software',
            'Amazon Web Services Consulting Providers',
            'Amazon Aurora Consulting Providers',
            'Amazon CloudFront Consulting Providers',
            'Amazon DynamoDB Consulting Providers',
            'Amazon EC2 Consulting Providers',
            'Amazon EMR Consulting Providers',
            'Amazon Kinesis Consulting Providers',
            'Amazon RDS Consulting Providers',
            'Amazon Redshift Consulting Providers',
            'Amazon S3 Consulting Providers',
            'AWS Lambda Consulting Providers',
            'AWS WAF Consulting Providers',
            'Other AWS Consulting Providers',
            'HubSpot Consulting Providers',
            'Infor Consulting Providers',
            'Infor CloudSuite Consulting Providers',
            'Infor CRM Consulting Providers',
            'Infor EAM Consulting Providers',
            'Infor ERP Consulting Providers',
            'Infor Lawson Consulting Providers',
            'Infor LN Consulting Providers',
            'Infor M3 Consulting Providers',
            'Infor SyteLine Consulting Providers',
            'Infor SunSystems Consulting Providers',
            'Infor Xi Consulting Providers',
            'Other Infor Consulting Providers',
            'Microsoft Consulting Providers',
            'Microsoft Azure Consulting Providers',
            'Microsoft Dynamics 365 Consulting Providers',
            'Microsoft Dynamics CRM Consulting Providers',
            'Microsoft Dynamics ERP Consulting Providers',
            'Microsoft Dynamics AX Consulting Providers',
            'Microsoft Dynamics GP Consulting Providers',
            'Microsoft Dynamics NAV Consulting Providers',
            'Microsoft Dynamics SL Consulting Providers',
            'Microsoft Office 365 Consulting Providers',
            'Other Microsoft Consulting Providers',
            'SharePoint Consulting Providers',
            'Oracle Consulting Providers',
            'Oracle Cloud Applications Consulting Providers',
            'Oracle CX - Customer Experience Cloud Consulting Providers',
            'Oracle EPM - Enterprise Performance Management Cloud Consulting Providers',
            'Oracle HCM - Human Capital Management Cloud Consulting Providers',
            'Oracle SCM - Supply Chain Management Cloud Consulting Providers',
            'Oracle Database Consulting Providers',
            'Oracle ERP Consulting Providers',
            'Oracle E-Business Suite Consulting Providers',
            'Oracle ERP Cloud Consulting Providers',
            'Oracle JD Edwards EnterpriseOne Consulting Providers',
            'Oracle PeopleSoft Consulting Providers',
            'Oracle Fusion Applications Consulting Providers',
            'Oracle Hyperion Consulting Providers',
            'Oracle Industry Solutions Consulting Providers',
            'Oracle Primavera Consulting Providers',
            'Oracle Siebel Consulting Providers',
            'Oracle Taleo Consulting Providers',
            'Other Oracle Consulting Providers',
            'Other Implementation Services Providers',
            'Pegasystems Consulting Providers',
            'Salesforce Consulting Providers',
            'FinancialForce Consulting Providers',
            'Other Salesforce Consulting Providers',
            'Sales Cloud Consulting Providers',
            'Salesforce CPQ Consulting Providers',
            'Salesforce CRM Consulting Providers',
            'Salesforce Analytics Cloud Consulting Providers',
            'Salesforce App Cloud Consulting Providers',
            'Salesforce Commerce Cloud Consulting Providers',
            'Salesforce Community Cloud Consulting Providers',
            'Salesforce Marketing Cloud Consulting Providers',
            'Salesforce Pardot Consulting Providers',
            'Service Cloud Consulting Providers',
            'SAP Consulting Providers',
            'Other SAP Consulting Providers',
            'SAP BI Consulting Providers',
            'SAP CRM Consulting Providers',
            'SAP EPM Consulting Providers',
            'SAP ERP Consulting Providers',
            'Business ByDesign Consulting Providers',
            'SAP Business All-in-One Consulting Providers',
            'SAP Business One Consulting Providers',
            'SAP FICO - Financial Accounting Consulting Providers',
            'SAP HR - Human Resources Consulting Providers',
            'SAP HANA Consulting Providers',
            'SAP Industry Solutions Consulting Providers',
            'SAP Mobile Platform Consulting Providers',
            'SAP PLM Consulting Providers',
            'SAP SCM Consulting Providers',
            'SAP SRM Consulting Providers',
            'Workday Consulting Providers',
            'Other Workday Consulting Providers',
            'Workday Financial Management Consulting Providers',
            'Workday Human Capital Management Consulting Providers',
            'Workday Planning Consulting Providers',
            'Workday Professional Services Automation Consulting Providers',
            'Workday Student Consulting Providers',
            'Solution Consulting Providers',
            'Business Intelligence (BI) Consulting Providers',
            'Cloud Consulting Providers',
            'Digital Transformation Consulting Providers',
            'IT Infrastructure Consulting Providers',
            'IT Strategy Consulting Providers',
            'Mobility Consulting Providers',
            'Other Solution Consulting Providers',
            'Quote-to-Cash Consulting Providers',
            'Staffing Services',
            'On-Demand Staffing Providers',
            'Other Staffing Services Providers',
            'Recruitment Agencies',
            'Staffing Agencies Providers',
            'Recruitment Marketing Agencies',
            'Translation Services',
            'Closed Captioning Services',
            'Interpretation Services',
            'Localization Services Providers',
            'App Localization Providers',
            'eLearning Localization Providers',
            'Game Localization Services',
            'Marketing Localization Providers',
            'Multimedia Localization Providers',
            'Software Localization Services',
            'Website Localization Services',
            'Multilingual Desktop Publishing Providers',
            'Transcription Services',
            'Translation Providers',
            'Audio Translation Services',
            'Document Translation Services',
            'Real-Time Text Translation Providers',
            'Video Translation Services',
            'Website Translation Providers',
            'Value-Added Resellers (VARs)',
            'Acumatica Channel Partners',
            'Adobe Channel Partners',
            'Amazon Web Services Channel Partners',
            'Autodesk Channel Partners',
            'Cisco Channel Partners',
            'Cisco Cloud Resellers',
            'Cisco Data Center Resellers',
            'Cisco Hardware Resellers',
            'Cisco Unified Communications Resellers',
            'Other Cisco Resellers',
            'Deltek Channel Partners',
            'Epicor Channel Partners',
            'IBM Channel Partners',
            'IBM Security VARs',
            'IBM Server VARs',
            'IBM Storage VARs',
            'Infor Channel Partners',
            'Infor CRM Resellers',
            'Infor EAM Resellers',
            'Infor ERP Resellers',
            'Infor Distribution FACTS Resellers',
            'Infor Distribution SX.e Resellers',
            'Infor LN Resellers',
            'Infor M3 Resellers',
            'Infor SyteLine Resellers',
            'Infor VISUAL Resellers',
            'Infor XA Resellers',
            'Infor SunSystems Resellers',
            'Infor Xi Resellers',
            'Other Infor Resellers',
            'Microsoft Channel Partners',
            'Microsoft Azure Resellers',
            'Microsoft Dynamics 365 Resellers',
            'Microsoft Dynamics CRM Resellers',
            'Microsoft Dynamics ERP Resellers',
            'Microsoft Dynamics AX Resellers',
            'Microsoft Dynamics GP Resellers',
            'Microsoft Dynamics NAV Resellers',
            'Microsoft Dynamics SL Resellers',
            'Microsoft Office 365 Resellers',
            'Other Microsoft Resellers',
            'SharePoint Resellers',
            'Oracle Channel Partners',
            'NetSuite Resellers',
            'Oracle Cloud Application Resellers',
            'Oracle Database Resellers',
            'Oracle ERP Resellers',
            'Oracle E-Business Suite Resellers',
            'Oracle JD Edwards EnterpriseOne Resellers',
            'Oracle PeopleSoft Resellers',
            'Oracle Fusion Applications Resellers',
            'Oracle Hyperion Resellers',
            'Oracle Primavera Resellers',
            'Oracle Siebel Resellers',
            'Other Oracle Resellers',
            'Other VARs',
            'Qlik Channel Partners',
            'Sage Channel Partners',
            'Other Sage Resellers',
            'Sage 100 Resellers',
            'Sage 300 Resellers',
            'Sage 500 Resellers',
            'Sage 50 Resellers',
            'Sage BusinessVision Resellers',
            'Sage BusinessWorks Resellers',
            'Sage CRM Resellers',
            'Sage Fixed Assets Resellers',
            'Sage HRMS Resellers',
            'Sage Intacct Channel Partners',
            'Sage X3 Resellers',
            'Salesforce Channel Partners',
            'SAP Channel Partners',
            'Other SAP Resellers',
            'SAP BusinessObjects Resellers',
            'SAP Cloud Resellers',
            'SAP ERP Resellers',
            'Business ByDesign Resellers',
            'SAP Business All-in-One Resellers',
            'SAP Business One Resellers',
            'SAP HANA Resellers',
            'SAP Hybris Resellers',
            'SAP SuccessFactors Resellers',
        ];
        $t = array_unique($s);
        foreach ($t as $item) {
            $c = Category::where('name',$item)->first();
            if (!$c) {
                $this->info($item);
            }
        }
        return;
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $url = 'https://www.g2crowd.com/categories/sales';
        $html = $this->ql->browser($url);
        $data = $html->rules([
            'name' => ['a','text'],
            'link' => ['a','href']
        ])->range('div.paper.mb-2>div')->query()->getData();
        var_dump($data);
        return;
        //抓取g2所有产品分类
        $url = 'https://www.g2crowd.com/categories?category_type=service';
        $html = $this->ql->browser($url);
        $data = $html->rules([
            'name' => ['h4.color-secondary','text'],
            'list' => ['h4~div.ml-2','html']
        ])->range('div.newspaper-columns__list-item.pb-1')->query()->getData(function ($item) {
            if (!isset($item['list'])) return $item;
            $item['list'] = $this->ql->html($item['list'])->rules([
                'name' => ['a.text-medium','text'],
                'link' => ['a.text-medium','href']
            ])->range('')->query()->getData();
            return $item;
        });
        var_dump($data);
        Storage::disk('local')->put('attachments/test5.html',json_encode($data));
        return;
        $page = 1;
        Submission::where('id','>=',1)->searchable();
        $submissions = Submission::where('type','review')->simplePaginate(100,['*'],'page',$page);
        while ($submissions->count() > 0) {
            foreach ($submissions as $submission) {
                $title = str_replace("”","",$submission->title);
                $title = str_replace("“","",$title);
                $title = str_replace("“< BR>","\n",$title);
                $title = str_replace("< BR>","\n",$title);
                $title = str_replace("amp;","",$title);
                if ($title != $submission->title) {
                    $submission->title = $title;
                    $submission->save();
                }
            }
            $this->info($page);
            $page++;
            $submissions = Submission::where('type','review')->simplePaginate(100,['*'],'page',$page);
        }

        return;
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $slug = '/products/salesforce-crm/reviews';
        $tag = Tag::find(39739);
        $page=1;
        $needBreak = false;
        while (true) {
            $data = $this->reviewData($slug,$page);
            if ($data->count() <= 0) {
                sleep(5);
                $data = $this->reviewData($slug,$page);
            }
            if ($data->count() <= 0 && $page == 1) {
                $this->info('tag:抓取点评失败');
                break;
            }
            if ($data->count() <= 0) {
                $this->info('tag:无数据，page:'.$page);
                break;
            }
            foreach ($data as $item) {
                $item['body'] = trim($item['body']);
                $item['body'] = trim($item['body'],'"');
                $item['body'] = trim($item['body']);
                if (strlen($item['body']) <= 50) continue;
                $this->info($item['link']);
                RateLimiter::instance()->hSet('review-submission-url',$item['link'],1);

                $sslug = app('pinyin')->abbr(strip_tags($item['body']));
                if (empty($sslug)) {
                    $sslug = 1;
                }
                if (strlen($sslug) > 50) {
                    $sslug = substr($sslug,0,50);
                }

                $submission = Submission::withTrashed()->where('slug', $sslug)->first();
                if ($submission) {
                    if (!isset($submission->data['origin_title'])) {
                        $this->info($submission->id);
                        $title = Translate::instance()->translate($item['body']);
                        $sdata = $submission->data;
                        $sdata['origin_title'] = $item['body'];
                        $submission->data = $sdata;
                        $submission->title = $title;
                        $submission->save();
                    }
                    continue;
                }

                preg_match('/\d+/',$item['star'],$rate_star);
                $title = $item['body'];
                if (config('app.env') == 'production' || $page <= 1) {
                    $title = Translate::instance()->translate($item['body']);
                }
                $submission = Submission::create([
                    'title'         => $title,
                    'slug'          => $this->slug($item['body']),
                    'type'          => 'review',
                    'category_id'   => $tag->id,
                    'group_id'      => 0,
                    'public'        => 1,
                    'rate'          => firstRate(),
                    'rate_star'     => $rate_star[0]/2,
                    'hide'          => 0,
                    'status'        => 0,
                    'user_id'       => 504,
                    'views'         => 1,
                    'created_at'    => date('Y-m-d H:i:s',strtotime($item['datetime'])),
                    'data' => [
                        'current_address_name' => '',
                        'current_address_longitude' => '',
                        'current_address_latitude' => '',
                        'category_ids' => [$tag->category_id],
                        'author_identity' => '',
                        'origin_author' => $item['name'],
                        'origin_title'  => $item['body'],
                        'img' => []
                    ]
                ]);
                Tag::multiSaveByIds($tag->id,$submission);
                $authors[$item['name']][] = $submission->id;
            }
            if ($needBreak) break;
            $this->info('page:'.$page);
            $page++;
        }
        return;

        $submissions = Submission::where('type','review')->where('id','<=',19332)->get();
        foreach ($submissions as $submission) {
            $submission->title = Translate::instance()->translate($submission->data['origin_title']);
            $submission->save();
        }
        return;
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $html = $ql->browser('https://www.g2crowd.com/products/salesforce-crm/reviews?page=1')->rules([
            'name' => ['div.font-weight-bold.mt-half.mb-4th','text'],
            'link' => ['a.pjax','href'],
            'star' => ['div.stars.large','class'],
            'datetime' => ['time','datetime'],
            'body' => ['div.d-f:gt(0)>.f-1','text']
        ])->range('div.mb-2.border-bottom')->query()->getData();
        Storage::disk('local')->put('attachments/test4.html',json_encode($html));
        var_dump($html);
        return;
        TagCategoryRel::sum('reviews');
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $tags = Tag::where('category_id','>=',43)->where('summary','')->get();
        foreach ($tags as $tag) {
            $slug = strtolower($tag->name);
            $slug = str_replace(' ','-',$slug);
            $url = 'https://www.g2crowd.com/products/'.$slug.'/details';
            $content = $ql->browser($url);
            $desc = $content->find('div.column.xlarge-8.xxlarge-9>div.row>div.xlarge-8.column>p')->eq(1)->text();
            if (empty($desc)) {
                $desc = $content->find('div.column.xlarge-7.xxlarge-8>p')->text();
                if (empty($desc)) {
                    $desc = $content->find('p.pt-half.product-show-description')->text();
                    //$desc = $content->find('div.column.large-8>p')->text();
                }
            }
            if (empty($desc)) continue;
            $summary = Translate::instance()->translate($desc);
            $tag->summary = $summary;
            $tag->description = $desc;
            $tag->save();
        }
        return;
        Translate::instance()->translate('hello');
        return;
        $tr = new TranslateClient('en', 'zh',['proxy'=>'socks5h://127.0.0.1:1080']);
        $en = $tr->translate('Salesforce helps businesses of all sizes accelerate sales, automate tasks and make smarter decisions so you can grow your business faster. Salesforce CRM offers: - Lead & Contact Management - Sales Opportunity Management - Workflow Rules & Automation - Customizable Reports & Dashboards - Mobile Application');
        var_dump($en);
        return;
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $content = $ql->browser('https://www.g2crowd.com/categories/crm',false,[
            '--proxy' => '127.0.0.1:1080',
            '--proxy-type' => 'socks5'
        ])->getHtml();
        //$company_description = $content->find('meta[name=Description]')->content;
        var_dump($content);
        //Storage::disk('local')->put('attachments/test4.html',$content);
        return;
        $ql = QueryList::getInstance();
        $cookies = Setting()->get('scraper_jianyu360_cookie','');
        $cookiesPcArr = explode('||',$cookies);
        $content = $ql->post('https://www.jianyu360.com/front/pcAjaxReq',[
            'pageNumber' => 1,
            'reqType' => 'bidSearch',
            'searchvalue' => 'SAP',
            'area' => '',
            'subtype' => '',
            'publishtime' => '',
            'selectType' => 'all',
            'minprice' => '',
            'maxprice' => '',
            'industry' => '',
            'tabularflag' => 'Y'
        ],[
            'timeout' => 60,
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => $cookiesPcArr[4]
            ]
        ])->getHtml();
        var_dump($content);
        return;
        $submissions = Submission::whereIn('group_id',[56])->get();
        foreach ($submissions as $submission) {
            Taggable::where('taggable_id',$submission->id)->where('taggable_type',get_class($submission))->update(['is_display'=>0]);
        }
        return;
        $domain = 'sogou';
        $members = RateLimiter::instance()->sMembers('proxy_ips_deleted_'.$domain);
        foreach ($members as $member) {
            deleteProxyIp($member,$domain);
        }
        return;
        $info['url'] = 'https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ3WmpSc0VnSmxiaWdBUAE?hl=en-US&gl=US&ceid=US%3Aen';
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $html = curlShadowsocks('https://news.google.com/articles/CBMiWmh0dHBzOi8vd3d3LnRoZXJlZ2lzdGVyLmNvLnVrLzIwMTgvMDkvMTMvc2FwX3NvdXRoX2FmcmljYV9wcm9iZV9jb3JydXB0aW9uX3dhdGVyX21pbmlzdHJ5L9IBAA?hl=en-US&gl=US&ceid=US%3Aen');

        $item['href'] = $ql->setHtml($html)->find('div.m2L3rb.eLNT1d')->children('a')->attr('href');
        var_dump($item['href']);
        return;

        $list = $ql->get($info['url'],[],[
            'proxy' => 'socks5h://127.0.0.1:1080',
        ])->rules([
            'title' => ['a.ipQwMb.Q7tWef>span','text'],
            'link'  => ['a.ipQwMb.Q7tWef','href'],
            'author' => ['.KbnJ8','text'],
            'dateTime' => ['time.WW6dff','datetime'],
            'description' => ['p.HO8did.Baotjf','text'],
            'image' => ['img.tvs3Id.dIH98c','src']
        ])->range('div.NiLAwe.y6IFtc.R7GTQ.keNKEd.j7vNaf.nID9nc')->query()->getData();

        foreach ($list as &$item) {
            sleep(1);
            $item['href'] = $ql->get('https://news.google.com/' . $item['link'], [], [
                'proxy' => 'socks5h://127.0.0.1:1080',
            ])->find('div.m2L3rb.eLNT1d')->children('a')->attrs('href');
        }
        var_dump($list);
        Storage::disk('local')->put('attachments/test4.html',json_encode($list));
        return;
        // Get the QueryList instance
        $ql = QueryList::getInstance();
// Get the login form
        $form = $ql->get('https://github.com/login')->find('form');

// Fill in the GitHub username and password
        $form->find('input[name=login]')->val('hank789');
        $form->find('input[name=password]')->val('wanghui8831');

// Serialize the form data
        $fromData = $form->serializeArray();
        $postData = [];
        foreach ($fromData as $item) {
            $postData[$item['name']] = $item['value'];
        }

// Submit the login form
        $actionUrl = 'https://github.com'.$form->attr('action');
        $rs = $ql->post($actionUrl,$postData);
        //var_dump($rs->getHtml());
// To determine whether the login is successful
// echo $ql->getHtml();

        $userName = $ql->get('https://github.com/')->find('span.text-bold')->text();
        //Storage::disk('local')->put('attachments/test4.html',$userName);
        var_dump($userName);
        if($userName)
        {
            echo 'Login successful ! Welcome:'.$userName;
        }else{
            echo 'Login failed !';
        }
        return;
        $wechat = new WechatSpider();
        $mp = WechatMpInfo::find(4);
        $items = $wechat->getGzhArticles($mp);
        var_dump($items);
        return;
        /*$sUrl = 'https://m.lagou.com/search.json?city=%E5%85%A8%E5%9B%BD&positionName=sap&pageNo=1&pageSize=15';
        $aHeader = [
            'Accept: application/json',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'Cookie: _ga=GA1.2.845934384.1535426841; user_trace_token=20180828112721-465c1caa-aa72-11e8-b24b-5254005c3644; LGUID=20180828112721-465c2202-aa72-11e8-b24b-5254005c3644; index_location_city=%E5%85%A8%E5%9B%BD; JSESSIONID=ABAAABAAAGCABCCD28DF8209A7B49B1E86DFDDA7FC4CB8F; _ga=GA1.3.845934384.1535426841; fromsite="zhihu.hank.com:8080"; utm_source=""; _gid=GA1.2.1118280405.1535619468; Hm_lvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1535455700,1535455777,1535455805,1535626070; _gat=1; LGSID=20180831103210-0fb55e88-acc6-11e8-be55-525400f775ce; PRE_UTM=; PRE_HOST=; PRE_SITE=; PRE_LAND=https%3A%2F%2Fwww.lagou.com%2F; LGRID=20180831103238-207ec83e-acc6-11e8-b30a-5254005c3644; Hm_lpvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1535682758',
            'Host: m.lagou.com',
            'Referer: https://m.lagou.com/search.html',
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
            'X-Requested-With: XMLHttpRequest'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        //curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aData));
        $sResult = curl_exec($ch);

        curl_close($ch);
        $s = json_decode($sResult,true);
        var_dump($s);*/
        $ql = QueryList::getInstance();
        $opts = [
            //Set the timeout time in seconds
            'timeout' => 10,
            'headers' => [
                'Host'   => 'weixin.sogou.com',
            ]
        ];
        $content = $ql->get('http://mp.weixin.qq.com/profile?src=3&timestamp=1536830900&ver=1&signature=NKQVmha9HAVDZdnvcqm2poIuSypgNmHb4Z8rZ8UUdwhtLSyUv2LnpneWG8ovrr7FjSoKABpEexJ7puIjcgQ-eA==',null,$opts);
        //var_dump($content->getHtml());
        return;



        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $cookiesApp = Setting()->get('scraper_jianyu360_app_cookie','');
        $cookiesAppArr = explode('||',$cookiesApp);
        //$ips = getProxyIps();
        $ips = ['139.217.24.50:3128'=>1];
        foreach ($ips as $ip=>$score) {
            $content = $ql->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($cookiesAppArr,$ip){
                //$r->setMethod('POST');
                $r->setUrl('https://www.jianyu360.com/jyapp/article/content/ABCY2EAfTIvJyksJFZhcHUJJzACHj1mZnB%2FKA4gPy43eFJzfzNUCZM%3D.html');
                /*$r->setRequestData([
                    'keywords' => '',
                    'publishtime' => '',
                    'timeslot' => '',
                    'area' => '',
                    'subtype' => '',
                    'minprice' => '',
                    'maxprice' => '',
                    'industry' => '',
                    'selectType' => 'title'
                ]);*/
                //$r->setTimeout(10000); // 10 seconds
                //$r->setDelay(3); // 3 seconds
                //$r->addHeader('Cookie','UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371');
                $r->setHeaders([
                    'Host'   => 'www.jianyu360.com',
                    'Referer'       => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Cookie' => $cookiesAppArr[0]
                ]);
                return $r;
            },false,[
                '--proxy' => $ip,
                '--proxy-type' => 'http'
            ]);
            $source_url = $content->find('a.original')->href;
            var_dump($source_url);
            $bid_html_body = $content->removeHead()->getHtml();
            if ($bid_html_body == '<html></html>') {
                var_dump($ip);
            }
            sleep(3);
        }
        return;


        // 安装时需要设置PhantomJS二进制文件路径
        //$ql->use(PhantomJs::class,config('services.phantomjs.path'));
        //$h = file_get_contents(storage_path().'/app/attachments/test3.html');
        //$ql->html($h);

        //$bid_html_body = $ql->removeHead()->getHtml();
        //$dom = new Dom();
        //$dom->load($bid_html_body);
        //$html = $dom->find('pre#h_content');
        //var_dump((string)$html);
        //return;
        //use Shadowsocks
        $content = $ql->browser('https://news.google.com/topics/CAAqIggKIhxDQkFTRHdvSkwyMHZNREZ5Y0RKakVnSmxiaWdBUAE',false,[
            '--proxy' => '127.0.0.1:1080',
            '--proxy-type' => 'socks5'
            //'proxy' => 'socks5h://127.0.0.1:1080',
        ])->rules([
            'title' => ['a.ipQwMb.Q7tWef>span','text'],
            'link'  => ['a.ipQwMb.Q7tWef','href'],
            'author' => ['.KbnJ8','text'],
            'description' => ['p.HO8did.Baotjf','text'],
            'image' => ['img.tvs3Id.dIH98c','src']
        ])->range('div.NiLAwe.y6IFtc.R7GTQ.keNKEd.j7vNaf.nID9nc')->query()->getData();
        var_dump($content);
        //Storage::disk('local')->put('attachments/test4.html',$content);
        return;
        $content = $ql->post('https://www.jianyu360.com/jylab/supsearch/getNewBids',[
            'pageNumber' => 2,
            'pageType' => ''
        ],[
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => 'UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371'
            ]
        ])->getHtml();
        var_dump($content);
        return;
        /*$content = $ql->post('https://www.jianyu360.com/front/pcAjaxReq',[
            'pageNumber' => 1,
            'reqType' => 'bidSearch',
            'searchvalue' => '系统',
            'area' => '',
            'subtype' => '',
            'publishtime' => '',
            'selectType' => 'title',
            'minprice' => '',
            'maxprice' => '',
            'industry' => '',
            'tabularflag' => 'Y'
        ],[
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => 'UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371'
            ]
        ])->getHtml();
        var_dump($content);
        return;*/
        //$ql = QueryList::get('https://www.lagou.com/jobs/list_前端?labelWords=&fromSearch=true&suginput=');
        $cookiesApp = Setting()->get('scraper_jianyu360_app_cookie','');
        $cookiesAppArr = explode('||',$cookiesApp);
        $content = $ql->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($cookiesAppArr){
            //$r->setMethod('POST');
            $r->setUrl('https://www.jianyu360.com/jyapp/article/content/ABCY2EAfTIvJyksJFZhcHUJJzACHj1mZnB%2FKA4gPy43eFJzfzNUCZM%3D.html');
            /*$r->setRequestData([
                'keywords' => '',
                'publishtime' => '',
                'timeslot' => '',
                'area' => '',
                'subtype' => '',
                'minprice' => '',
                'maxprice' => '',
                'industry' => '',
                'selectType' => 'title'
            ]);*/
            //$r->setTimeout(10000); // 10 seconds
            //$r->setDelay(3); // 3 seconds
            //$r->addHeader('Cookie','UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371');
            $r->setHeaders([
                'Host'   => 'www.jianyu360.com',
                'Referer'       => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Cookie' => $cookiesAppArr[0]
            ]);
            return $r;
        },false,[
            '--proxy' => 'http://89.22.175.42:8080',
            '--proxy-type' => 'http'
        ]);
        $source_url = $content->find('a.original')->href;
        var_dump($source_url);
        $bid_html_body = $content->removeHead()->getHtml();
        var_dump($bid_html_body);
        $dom = new Dom();
        $dom->load($bid_html_body);
        $html = $dom->find('pre#h_content');
        var_dump($html->__toString());
        //$content = $ql->browser('http://36kr.com/p/5151347.html?ktm_source=feed')->find('link[href*=.ico]')->href;
        var_dump($source_url);
        //var_dump($bid_html_body);

        //Storage::disk('local')->put('attachments/test1.html',$content);
        return;
    }

    public function getHtmlData($i) {
        if ($i == 4) return $i;
        return null;
    }

    protected function reviewData($slug,$page) {
        $html = $this->ql->browser('https://www.g2crowd.com'.$slug.'?page='.$page)->rules([
            'name' => ['div.font-weight-bold.mt-half.mb-4th','text'],
            'link' => ['a.pjax','href'],
            'star' => ['div.stars.large','class'],
            'datetime' => ['time','datetime'],
            'body' => ['div.d-f:gt(0)>.f-1','text']
        ])->range('div.mb-2.border-bottom')->query()->getData();
        return $html;
    }
}
