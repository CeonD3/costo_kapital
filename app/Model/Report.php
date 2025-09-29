<?php

namespace App\Model;

use App\Utilitarian\{Crypt, FG};
use App\Model\{SpreadsheetManage};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Report extends Model
{

	protected $fillable = ['reports'];

	const COMPANY_TYPE = 1;
	const SECTORIAL_TYPE = 2;

	public static function getAllReport($args)
	{
		extract($args);
		$system = array();
		$spreadsheetManage = new SpreadsheetManage();

		$result = $spreadsheetManage->initCalculate(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['calculate'] = $result['data'];

		$result = $spreadsheetManage->initCurvePerformance(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['curve'] = $result['data'];

		$result = $spreadsheetManage->flowsProject(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['flow'] = $result['data'];

		$result = $spreadsheetManage->initCurvePerformanceProject(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['curve_project'] = $result['data'];

		$result = $spreadsheetManage->initStructureDeveloped(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['developed']['structure'] = $result['data'];

		$result = $spreadsheetManage->initParameterDeveloped(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['developed']['parameter'] = $result['data'];

		$result = $spreadsheetManage->initAverageDeveloped(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['developed']['average'] = $result['data'];

		$result = $spreadsheetManage->initStructureEmerging(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['emerging']['structure'] = $result['data'];

		$result = $spreadsheetManage->initParameterEmerging(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['emerging']['parameter'] = $result['data'];

		$result = $spreadsheetManage->initAverageEmerging(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['emerging']['average'] = $result['data'];

		$result = $spreadsheetManage->initStructureCompany(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['company']['structure'] = $result['data'];

		$result = $spreadsheetManage->initParameterCompany(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['company']['parameter'] = $result['data'];

		$result = $spreadsheetManage->initAverageDolaresCompany(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['company']['dolares'] = $result['data'];

		$result = $spreadsheetManage->initAverageNationalCompany(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['company']['national'] = $result['data'];

		$result = $spreadsheetManage->initReportCompany(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['report'] = $result['data'];

		$result = $spreadsheetManage->initReportSectorial(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['sectorial'] = $result['data'];

		$result = $spreadsheetManage->initComparation(compact('filename'));
		if (!$result['success']) {
			throw new \Exception($result['message']);
		}
		$system['comparation'] = $result['data'];

		return $system;
	}
}
