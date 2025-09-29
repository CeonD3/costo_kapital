<?php

    // require_once __DIR__ . "/api.php"; 
    $map->attach('reports.', '/report', function ($map) {

        $map->get('kapital', '/kapital/{key}',[
            'Controller' => 'App\Controllers\ReportController',
            'Action' => 'kapital'
        ]);

        $map->get('valora', '/valora/{key}',[
            'Controller' => 'App\Controllers\ReportController',
            'Action' => 'valora'
        ]);

    });

    $map->attach('api.', '/api', function ($map) {

        $map->attach('finance.', '/finance', function ($map) {

            $map->post('industries', '/industries',[
                'Controller' => 'App\Controllers\FinanceController',
                'Action' => 'industries'
            ]);

            $map->post('project.remove', '/projects/{id}/remove',[
                'Controller' => 'App\Controllers\FinanceController',
                'Action' => 'removeProject'
            ]);
        
        });

        $map->attach('auth.', '/auth', function ($map) {

            $map->post('signin', '/signin',[
                'Controller' => 'App\Controllers\AuthController',
                'Action' => 'signin'
            ]);

            $map->post('signup', '/signup',[
                'Controller' => 'App\Controllers\AuthController',
                'Action' => 'signup'
            ]);

            $map->post('authcms', '/authcms',[
                'Controller' => 'App\Controllers\AuthController',
                'Action' => 'authcms'
            ]);
        
        });

        $map->attach('kapital.', '/kapital', function ($map) {

            $map->post('form', '/form',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'form'
            ]);
        
            $map->post('store', '/store',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'store'
            ]);

            $map->post('taxrate', '/taxrate',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'taxrate'
            ]);

            $map->post('projects', '/users/{userId}/projects',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'projects'
            ]);
        
            $map->post('update', '/users/{userId}/templates/{uid}/update',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'update'
            ]);

            $map->post('analysis', '/users/{userId}/templates/{uid}/analysis',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'analysis'
            ]);

            $map->attach('report.', '/users/{userId}/templates/{uid}', function ($map) {

                $map->post('generateReport', '/reports/generate',[
                    'Controller' => 'App\Controllers\KapitalController',
                    'Action' => 'generateReport'
                ]);

                $map->post('listReport', '/reports/list',[
                    'Controller' => 'App\Controllers\KapitalController',
                    'Action' => 'listReport'
                ]);

                $map->post('indexReport', '/reports/{id}',[
                    'Controller' => 'App\Controllers\KapitalController',
                    'Action' => 'indexReport'
                ]);

                $map->post('contentReport', '/reports/{id}/content',[
                    'Controller' => 'App\Controllers\KapitalController',
                    'Action' => 'contentReport'
                ]);

                $map->post('graphReport', '/reports/{id}/complement',[
                    'Controller' => 'App\Controllers\KapitalController',
                    'Action' => 'graphReport'
                ]);

                $map->get('showReport', '/reports/{id}/show',[
                    'Controller' => 'App\Controllers\KapitalController',
                    'Action' => 'showReport'
                ]);

            });
        
            $map->post('detailResult', '/users/{userId}/templates/{uid}/result/detail',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'detailResult'
            ]);

            $map->post('detailAnalysis', '/users/{userId}/templates/{uid}/analysis/detail',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'detailAnalysis'
            ]);

            $map->post('costAnalysis', '/users/{userId}/templates/{uid}/analysis/cost',[
                'Controller' => 'App\Controllers\KapitalController',
                'Action' => 'costAnalysis'
            ]);
        
        });

        $map->attach('valora.', '/valora', function ($map) {

            $map->post('form', '/form',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'form'
            ]);
        
            $map->post('store', '/store',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'store'
            ]);

            $map->post('bvl', '/bvl',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'bvl'
            ]);

            $map->post('upload', '/upload',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'upload'
            ]);

            $map->post('projects', '/users/{userId}/projects',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'projects'
            ]);

            $map->post('update', '/users/{userId}/templates/{uid}/update',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'update'
            ]);
        
            $map->post('balance', '/users/{userId}/templates/{uid}/balance',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'balance'
            ]);
            
            $map->post('result', '/users/{userId}/templates/{uid}/result',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'result'
            ]);
        
            $map->post('detailResult', '/users/{userId}/templates/{uid}/result/detail',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'detailResult'
            ]);

            $map->post('analysis', '/users/{userId}/templates/{uid}/analysis',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'analysis'
            ]);

            $map->post('detailAnalysis', '/users/{userId}/templates/{uid}/analysis/detail',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'detailAnalysis'
            ]);

            $map->post('costAnalysis', '/users/{userId}/templates/{uid}/analysis/cost',[
                'Controller' => 'App\Controllers\ValoraController',
                'Action' => 'costAnalysis'
            ]);
            
            $map->attach('report.', '/users/{userId}/templates/{uid}', function ($map) {

                $map->post('generateReport', '/reports/generate',[
                    'Controller' => 'App\Controllers\ValoraController',
                    'Action' => 'generateReport'
                ]);

                $map->post('listReport', '/reports/list',[
                    'Controller' => 'App\Controllers\ValoraController',
                    'Action' => 'listReport'
                ]);

                $map->post('indexReport', '/reports/{id}',[
                    'Controller' => 'App\Controllers\ValoraController',
                    'Action' => 'indexReport'
                ]);

                $map->post('contentReport', '/reports/{id}/content',[
                    'Controller' => 'App\Controllers\ValoraController',
                    'Action' => 'contentReport'
                ]);

                $map->post('graphReport', '/reports/{id}/complement',[
                    'Controller' => 'App\Controllers\ValoraController',
                    'Action' => 'graphReport'
                ]);

                $map->get('showReport', '/reports/{id}/show',[
                    'Controller' => 'App\Controllers\ValoraController',
                    'Action' => 'showReport'
                ]);

            });
        
        });

        $map->attach('admin.', '/admin', function ($map) {

            $map->attach('master.', '/master', function ($map) {

                $map->get('template.list', '/templates/list',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'listsTemplate'
                ]);

                $map->post('template.remove', '/templates/remove',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'removeTemplate'
                ]);
                
                $map->post('template.save', '/templates/save',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'manageTemplate'
                ]);
    
            });

            $map->attach('kapital.', '/kapital', function ($map) {

                $map->get('report.list', '/reports/list',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'listReportKapital'
                ]);

                $map->get('report.show', '/reports/{id}/show',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'showReportKapital'
                ]);

                $map->get('report.create', '/reports/create',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'createReportKapital'
                ]);
                
            });

            $map->attach('valora.', '/valora', function ($map) {

                $map->get('report.list', '/reports/list',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'listReportValora'
                ]);

                $map->get('report.show', '/reports/{id}/show',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'showReportValora'
                ]);

                $map->get('report.create', '/reports/create',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'createReportValora'
                ]);
                
            });

            $map->attach('report.', '/reports', function ($map) {

                $map->post('remove', '/remove',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'removeDesign'
                ]);

                $map->post('save', '/save',[
                    'Controller' => 'App\Controllers\AdminController',
                    'Action' => 'saveDesign'
                ]);

            });
            
            $map->attach('onedrive.', '/onedrive', function ($map) {

                $map->post('overview', '/overview', [
                    'Controller' => 'App\Controllers\OnedriveController',
                    'Action' => 'overview'
                ]);
            
                $map->post('authUrl', '/authUrl', [
                    'Controller' => 'App\Controllers\OnedriveController',
                    'Action' => 'authUrl'
                ]);
            
                $map->post('signout', '/signout', [
                    'Controller' => 'App\Controllers\OnedriveController',
                    'Action' => 'signout'
                ]);
            
            });
            
            
            $map->attach('configuration.', '/configuration', function ($map) {

                $map->post('show', '/show',[
                    'Controller' => 'App\Controllers\ConfigurationController',
                    'Action' => 'show'
                ]);

                $map->post('update', '/update',[
                    'Controller' => 'App\Controllers\ConfigurationController',
                    'Action' => 'update'
                ]);
                
            });

        });
    });

    $map->attach('microsoft.', '/microsoft', function ($map) {

        $map->get('onedrive.signin', '/onedrive/signin',[
            'Controller' => 'App\Controllers\OnedriveController',
            'Action' => 'signin'
        ]);

    });
    
    $map->get('kblive', '/kblive',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'kblive'
    ]);

    $map->get('inicio', '/',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'inicioAction'
    ]);

    $map->get('Costo', '/costo-capital',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'costoCapitalAction'
    ]);

    $map->get('Costo-reporte', '/reportes',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'costoCapitalReporteAction'
    ]);

    $map->get('costo-equipo', '/creditos',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'equipoAction'
    ]);

    $map->get('Contacto', '/contacto',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'contactoAction'
    ]);

    $map->post('sing-out', '/sing-out',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'singoutAction'
    ]);

    $map->post('register-user', '/register-user',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'registerUserAction'
    ]);

    $map->post('sendContact', '/sendContact',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'sendContact'
    ]);

    $map->post('system.onCreateRecord', '/system/onCreateRecord',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onCreateRecord'
    ]);

    $map->post('system.onCreateSectorial', '/system/onCreateSectorial',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onCreateSectorial'
    ]);

    $map->get('system.initRecord', '/historial',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initRecord'
    ]);

    $map->get('Resultados-code', '/calcula/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initCalculate'
    ]);

    $map->get('system.parameter', '/rendimiento/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initCurvePerformance'
    ]);

    $map->get('system.initStructureDeveloped', '/estructura-mercado-desarrollado/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initStructureDeveloped'
    ]);

    $map->get('system.initParameterDeveloped', '/parametros-mercado-desarrollado/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initParameterDeveloped'
    ]);
    
    $map->get('system.initAverageDeveloped', '/promedio-mercado-desarrollado/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initAverageDeveloped'
    ]);

    $map->get('system.initStructureEmerging', '/estructura-mercado-emergente/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initStructureEmerging'
    ]);

    $map->get('system.initParameterEmerging', '/parametros-mercado-emergente/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initParameterEmerging'
    ]);

    $map->get('system.initAverageEmerging', '/promedio-mercado-emergente/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initAverageEmerging'
    ]);

    $map->get('system.initStructureCompany', '/estructura-empresa/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initStructureCompany'
    ]);

    $map->get('system.initParameterCompany', '/parametros-empresa/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initParameterCompany'
    ]);

    $map->get('system.initAverageDolaresCompany', '/promedio-dolares-empresa/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initAverageDolaresCompany'
    ]);

    $map->get('system.initAverageNationalCompany', '/promedio-nacional-empresa/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initAverageNationalCompany'
    ]);

    $map->get('system.initReportCompany', '/reporte/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initReportCompany'
    ]);

    $map->get('system.initReportSectorial', '/reporte-sectorial/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initReportSectorial'
    ]);

    $map->get('system.listDocumentsReport', '/documentos/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'listDocumentsReport'
    ]);

    $map->get('growth-code', '/desarrollo/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'growthAction'
    ]);

    $map->get('employee-growth-code', '/empleado-desarrollo/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'growthEmployeeAction'
    ]);
    
    $map->get('sectors-code', '/sectores/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'SectorsAction'
    ]);
    
    $map->get('employee-sectors-code', '/empleado-sectores/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'SectorsEmployeeAction'
    ]);
    
    $map->get('emergencies-code', '/emergencias/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'emergenciesAction'
    ]);
    
    $map->get('employee-emergencies-code', '/empleado-emergencias/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'emergenciesEmployeeAction'
    ]);

    $map->get('investments-code', '/inversiones/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'investmentsAction'
    ]);

    $map->get('employee-investments-code', '/empleado-inversiones/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'investmentsEmployeeAction'
    ]);

    $map->get('rates-code', '/tasas/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'ratesAction'
    ]);

    $map->get('employee-rates-code', '/empleado-tasas/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'ratesEmployeeAction'
    ]);

    $map->get('system.flowsProject', '/flujos/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'flowsProject'
    ]);

    $map->post('system.onCalculation', '/system/onCalculation',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onCalculation'
    ]);

    $map->post('system.onCountryEmerging', '/system/onCountryEmerging',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onCountryEmerging'
    ]);

    $map->post('system.onDevaluationEmerging', '/system/onDevaluationEmerging',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onDevaluationEmerging'
    ]);

    $map->post('system.onPercentageCurrencyCompany', '/system/onPercentageCurrencyCompany',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onPercentageCurrencyCompany'
    ]);

    $map->post('system.onPercentageInvestment', '/system/onPercentageInvestment',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onPercentageInvestment'
    ]);

    $map->post('system.onCurvePerformance', '/system/onCurvePerformance',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onCurvePerformance'
    ]);

    $map->post('costCalculationSectorUser', '/system/costCalculationSectorUser',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'costCalculationSectorUser'
    ]);

    $map->post('costCalculationInvesmentUser', '/system/costCalculationInvesmentUser',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'costCalculationInvesmentUser'
    ]);

    $map->post('system.onCalculationFlow', '/system/onCalculationFlow',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onCalculationFlow'
    ]);

    $map->post('system.onCalculationDetailFlow', '/system/onCalculationDetailFlow',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'onCalculationDetailFlow'
    ]);

    $map->post('deleteReport', '/system/deleteReport',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'deleteReport'
    ]);

    $map->get('profile', '/perfil',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'initProfile'
    ]);

    $map->post('system.getAllReport', '/system/getAllReport',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'getAllReport'
    ]);

    $map->post('system.filterRiskLevel', '/system/filterRiskLevel',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'filterRiskLevel'
    ]);

    $map->post('payment.transfer', '/pagos/transfer',[
        'Controller' => 'App\Controllers\PaymentController',
        'Action' => 'transfer'
    ]);

    $map->get('system.file', '/reporte/documento/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initFile'
    ]);

    $map->get('system.document.download', '/reporte/{code}/documento/{id}/download',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'downloadDocument'
    ]);

    $map->get('system.document.compra', '/reporte/{code}/documento/{id}/compra',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initPaymentReport'
    ]);

    $map->get('system.comparation', '/comparacion/{code}',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'initComparation'
    ]);

    $map->post('system.report.register', '/system/reporte/register',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'registerReport'
    ]);

if ((isset($_SESSION['user']) && is_object($_SESSION['user']))) {

    $map->get('system.document.view', '/admin/documento/{id}/download',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'viewDocument'
    ]);

    //ADMINISTRACION
    $map->get('admin', '/admin',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'InicioAction'
    ]);
    
    $map->get('landing', '/admin/landing-home',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'InicioAction'
    ]);
    // BEGIN REPORTES ADMIN
    $map->get('admin.design.create', '/admin/reportes/crear',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'create'
    ]);

    $map->get('admin.design.edit', '/admin/reportes/editar/{id}',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'edit'
    ]);

    $map->get('admin.design.list', '/admin/reportes',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'initReportAdmin'
    ]);

    $map->post('admin.design.save', '/admin/report/save',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'saveDesign'
    ]);

    $map->post('admin.design.remove', '/admin/report/remove',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'removeDesign'
    ]);

    $map->get('admin.valora.design.list', '/admin/valora/reportes',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'initReportValoraAdmin'
    ]);

    $map->get('admin.valora.design.create', '/admin/valora/reportes/crear',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'createValora'
    ]);

    $map->get('admin.valora.design.edit', '/admin/valora/reportes/editar/{id}',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'editValora'
    ]);

    // END REPORTES ADMIN
    $map->post('HomeUpdate', '/admin/home-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'HomeUpdateAction'
    ]);

    $map->post('GlosarioUpdate', '/admin/glosario-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'glosarioUpdateAction'
    ]);

    $map->get('landing-detalle', '/admin/landing-home-detalle',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'HomeDetalleAction'
    ]);

    $map->post('servicio-item', '/admin/get-servicio-item',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'getServicioItemAction'
    ]);

    $map->post('update-servicio-item', '/admin/servicio-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'setServicioItemAction'
    ]);

    $map->post('add-servicio-item', '/admin/servicio-add',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'addServicioItemAction'
    ]);
    
    $map->post('add-industria-item', '/admin/industria-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'addIndustriaItemAction'
    ]);

    $map->post('companias-item', '/admin/getcompanias',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'getcompanias'
    ]);

    $map->post('add-compania-item', '/admin/compania-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'addCompaniaItemAction'
    ]);

    $map->post('delete-compania-item', '/admin/compania-delete',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'deleteCompaniaItemAction'
    ]);

    $map->post('delete-industria-item', '/admin/industria-delete',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'deleteIndustriaItemAction'
    ]);

    $map->post('delete-servicio-item', '/admin/delete-servicio-item',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'deleteServicioItemAction'
    ]);

    $map->get('costo-capital-item', '/admin/costo-capital-detalle',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'costoCapitalAction'
    ]);

    $map->get('costo-capital-reporte', '/admin/costo-capital-reporte',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'costoCapitalReporteAction'
    ]);

    $map->post('update-servicio-costo-item', '/admin/servicio-costo-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'setServicioCostoItemAction'
    ]);

    $map->post('add-servicio-costo-item', '/admin/servicio-costo-add',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'addServicioCostoItemAction'
    ]);


    $map->get('admin-contacto', '/admin/contacto',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'contactoAction'
    ]);

    $map->get('admin.equipo', '/admin/creditos',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'teamAction'
    ]);

    $map->post('admin.equipo.get', '/admin/get-team-item',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'getTeamItem'
    ]);

    $map->post('admin.teamTex.save', '/admin/team-text-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'saveTeamTex'
    ]);

    $map->post('admin.team.delete', '/admin/team-delete',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'deleteTeam'
    ]);

    $map->post('admin.team.add', '/admin/team-add',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'addTeam'
    ]);

    $map->post('admin.team.update', '/admin/team-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'updateTeam'
    ]);

    $map->get('admin-information', '/admin/informacion',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'information'
    ]);

    $map->get('admin-glosario', '/admin/glosario',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'glosario'
    ]);

    $map->post('admin.information.save', '/admin/information/save',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'informationSave'
    ]);

    $map->post('update-contacto', '/admin/contacto-update',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'setContactoAction'
    ]);
    
    $map->get('list-template', '/admin/plantillas',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'listTemplate'
    ]);

    $map->post('remove-template', '/admin/removeTemplate',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'removeTemplate'
    ]);

    $map->post('manage-template', '/admin/manageTemplate',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'manageTemplate'
    ]);

    $map->get('downloadMasterTemplate', '/template/master/{file}',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'downloadMasterTemplate'
    ]);

    $map->post('updateNewVersion', '/system/updateNewVersion',[
        'Controller' => 'App\Controllers\SystemController',
        'Action' => 'updateNewVersion'
    ]);

    $map->get('admin-contacts', '/admin/contactos',[
        'Controller' => 'App\Controllers\AdminController',
        'Action' => 'listContacts'
    ]);

    $map->post('system.onSavePassword', '/system/onSavePassword',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'onSavePassword'
    ]);

    $map->post('system.onSaveUserData', '/system/onSaveUserData',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'onSaveUserData'
    ]);

    $map->get('system.payment.index', '/admin/pagos',[
        'Controller' => 'App\Controllers\PaymentController',
        'Action' => 'index'
    ]);

    $map->post('system.payment.save', '/admin/pagos/save',[
        'Controller' => 'App\Controllers\PaymentController',
        'Action' => 'save'
    ]);

    $map->get('system.payment.bitacoras', '/admin/bitacoras-ventas',[
        'Controller' => 'App\Controllers\PaymentController',
        'Action' => 'bitacoras'
    ]);

    $map->post('system.transfer.save', '/admin/transfer/save',[
        'Controller' => 'App\Controllers\PaymentController',
        'Action' => 'checkout'
    ]);

    $map->get('system.account.index', '/admin/cuentas',[
        'Controller' => 'App\Controllers\AccountController',
        'Action' => 'index'
    ]);

    $map->attach('onedrive.', '/admin/onedrive', function ($map) {

        $map->post('overview', '/overview', [
            'Controller' => 'App\Controllers\OnedriveController',
            'Action' => 'overview'
        ]);
    
        $map->post('authUrl', '/authUrl', [
            'Controller' => 'App\Controllers\OnedriveController',
            'Action' => 'authUrl'
        ]);
    
        $map->post('signout', '/signout', [
            'Controller' => 'App\Controllers\OnedriveController',
            'Action' => 'signout'
        ]);
    
    });    

 } else {
    $map->get('login', '/login',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'loginAction'
    ]);

    $map->post('postLogin', '/postLogin',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'postLogin'
    ]);  

    $map->post('forgotPasswordUser', '/forgotPasswordUser',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'forgotPasswordUser'
    ]);

    $map->get('recover-password', '/recover-password/{token}',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'recoverPassword'
    ]);

    $map->post('post-recover-password', '/recover-password/{token}',[
        'Controller' => 'App\Controllers\HomeController',
        'Action' => 'postRecoverPassword'
    ]);
}

?>