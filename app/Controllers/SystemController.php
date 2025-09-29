<?php 
namespace App\Controllers;

use App\Model\{System};

class SystemController extends BaseController
{
	public function initRecord($request) {
		$system = new System();
		$rsp = $system->initRecord($request);
		return $this->renderHTML('system/inicio.twig',compact('rsp'));
	}

    public function initCalculate($request) {
		$system = new System();
		$rsp = $system->initCalculate($request);
		return $this->renderHTML('system/calculation.twig', compact('rsp'));
	}

	public function initCurvePerformance($request) {
		$system = new System();
		$rsp = $system->initCurvePerformance($request);
		return $this->renderHTML('system/curve.twig', compact('rsp'));
	}
	
	public function onCurvePerformance($request) {
		$system = new System();
		return $system->onCurvePerformance($request);
	}

	public function initStructureDeveloped($request) {
		$system = new System();
		$rsp = $system->initStructureDeveloped($request);
		return $this->renderHTML('system/structure.developed.twig', compact('rsp'));
	}

	public function initParameterDeveloped($request) {
		$system = new System();
		$rsp = $system->initParameterDeveloped($request);
		return $this->renderHTML('system/parameter.developed.twig', compact('rsp'));
	}

	public function initAverageDeveloped($request) {
		$system = new System();
		$rsp = $system->initAverageDeveloped($request);
		return $this->renderHTML('system/average.developed.twig', compact('rsp'));
	}

	public function initStructureEmerging($request) {
		$system = new System();
		$rsp = $system->initStructureEmerging($request);
		return $this->renderHTML('system/structure.emerging.twig', compact('rsp'));
	}

	public function initParameterEmerging($request) {
		$system = new System();
		$rsp = $system->initParameterEmerging($request);
		return $this->renderHTML('system/parameter.emerging.twig', compact('rsp'));
	}

	public function initAverageEmerging($request) {
		$system = new System();
		$rsp = $system->initAverageEmerging($request);
		return $this->renderHTML('system/average.emerging.twig', compact('rsp'));
	}

	public function initStructureCompany($request) {
		$system = new System();
		$rsp = $system->initStructureCompany($request);
		return $this->renderHTML('system/structure.company.twig', compact('rsp'));
	}

	public function initParameterCompany($request) {
		$system = new System();
		$rsp = $system->initParameterCompany($request);
		return $this->renderHTML('system/parameter.company.twig', compact('rsp'));
	}

	public function initAverageDolaresCompany($request) {
		$system = new System();
		$rsp = $system->initAverageDolaresCompany($request);
		return $this->renderHTML('system/average.dolares.company.twig', compact('rsp'));
	}

	public function initAverageNationalCompany($request) {
		$system = new System();
		$rsp = $system->initAverageNationalCompany($request);
		return $this->renderHTML('system/average.national.company.twig', compact('rsp'));
	}

	public function initReportCompany($request) {
		$system = new System();
		$rsp = $system->initReportCompany($request);
		return $this->renderHTML('system/report.company.twig', compact('rsp'));
	}

	public function initReportSectorial($request) {
		$system = new System();
		$rsp = $system->initReportSectorial($request);
		return $this->renderHTML('system/report.sectorial.twig', compact('rsp'));
	}

	public function listDocumentsReport($request) {
		$system = new System();
		$rsp = $system->listDocumentsReport($request);
		return $this->renderHTML('system/documents.twig', compact('rsp'));
	}

	public function initPaymentReport($request) {
		$system = new System();
		$rsp = $system->initPaymentReport($request);
		return $this->renderHTML('system/payment.twig', compact('rsp'));
	}

	public function GrowthAction($request) {
		$system = new System();
		$rsp = $system->marketDeveloperBonusReport($request);
		return $this->renderHTML('system/bonus/growth.twig', compact('rsp'));
	}

	public function growthEmployeeAction($request) {
		$system = new System();
		$rsp = $system->marketDeveloperEmployeeReport($request);
		return $this->renderHTML('system/bonus/growth.twig', compact('rsp'));
	}
	
	public function sectorsAction($request) {
		$system = new System();
		$rsp = $system->initCalculateSectorBonusReport($request);
		return $this->renderHTML('system/bonus/sectors.twig', compact('rsp'));
	}

	public function SectorsEmployeeAction($request) {
		$system = new System();
		$rsp = $system->initCalculateSectorEmployeeReport($request);
		return $this->renderHTML('system/bonus/sectors.twig', compact('rsp'));
	}

	public function emergenciesAction($request) {
		$system = new System();
		$rsp = $system->initCalculateSectorBonusReport2($request);
		return $this->renderHTML('system/bonus/emergencies.twig', compact('rsp'));
	}

	public function emergenciesEmployeeAction($request) {
		$system = new System();
		$rsp = $system->initCalculateSectorEmployeeReport2($request);
		return $this->renderHTML('system/bonus/emergencies.twig', compact('rsp'));
	}

	public function investmentsAction($request) {
		$system = new System();
		$rsp = $system->initCalculateInvestmentBonusReport($request);
		return $this->renderHTML('system/bonus/investments.twig', compact('rsp'));
	}

	public function investmentsEmployeeAction($request) {
		$system = new System();
		$rsp = $system->initCalculateInvestmentEmployeeReport($request);
		return $this->renderHTML('system/bonus/investments.twig', compact('rsp'));
	}

	public function ratesAction($request) {
		$system = new System();
		$rsp = $system->ratesCostBonusReport($request);
		return $this->renderHTML('system/bonus/rates.twig', compact('rsp'));
	}

	public function ratesEmployeeAction($request) {
		$system = new System();
		$rsp = $system->ratesCostEmployeeReport($request);
		return $this->renderHTML('system/bonus/rates.twig', compact('rsp'));
	}

	public function initComparation($request) {
		$system = new System();
		$rsp = $system->initComparation($request);
		return $this->renderHTML('system/comparation.twig', compact('rsp'));
	}

	public function onCreateRecord($request) {
		$system = new System();
		return $system->onCreateRecord($request);
	}

	public function onCreateSectorial($request) {
		$system = new System();
		$_POST['sectorial'] = 2;
		return $system->onCreateRecord($request);
	}

	public function flowsProject($request) {
		$system = new System();
		$rsp = $system->flowsProject($request);
		return $this->renderHTML('system/flows.twig', compact('rsp'));
	}

	public function onCalculation($request) {
		$system = new System();
		return $system->onCalculation($request);
	}

	public function onCountryEmerging($request) {
		$system = new System();
		return $system->onCountryEmerging($request);
	}

	public function onDevaluationEmerging($request) {
		$system = new System();
		return $system->onDevaluationEmerging($request);
	}

	public function onPercentageCurrencyCompany($request) {
		$system = new System();
		return $system->onPercentageCurrencyCompany($request);
	}

	public function onPercentageInvestment($request) {
		$system = new System();
		return $system->onPercentageInvestment($request);
	}

	public function costCalculationSectorUser($request) {
		$system = new System();
		return $system->costCalculationSectorUser($request);
	}

	public function costCalculationInvesmentUser($request) {
		$system = new System();
		return $system->costCalculationInvesmentUser($request);
	}

	public function onCalculationFlow($request) {
		$system = new System();
		return $system->onCalculationFlow($request);
	}

	public function onCalculationDetailFlow($request) {
		$system = new System();
		return $system->onCalculationDetailFlow($request);
	}

	public function deleteReport($request) {
		$system = new System();
		return $system->deleteReport($request);
	}

	public function updateNewVersion($request) {
		$system = new System();
		return $system->updateNewVersion($request);
	}

	public function getAllReport($request) {
		$system = new System();
		return $system->getAllReport($request);
	}

	public function filterRiskLevel($request) {
		$system = new System();
		return $system->filterRiskLevel($request);
	}

	public function initFile($request) {
		$system = new System();
		$rsp = $system->initFile($request);
		return $this->renderHTML('system/file.twig', compact('rsp'));
	}

	public function downloadDocument($request) {
		$system = new System();
		$rsp = $system->downloadDocument($request);
		return $this->renderHTML('system/file.twig', compact('rsp'));
	}

	public function viewDocument($request) {
		$system = new System();
		$rsp = $system->viewDocument($request);
		return $this->renderHTML('system/file.twig', compact('rsp'));
	}

	public function registerReport($request) {
		$system = new System();
		return $system->registerReport($request);
	}
}

 ?>