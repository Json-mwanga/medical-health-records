<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../config/database.php'; // adjust path as needed

try {
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    file_put_contents('debug_post.txt', print_r($data, true)); // Debugging line
    $ward = $data['ward'];
    $date = $data['date'];

    // 1. Insert or update census_sheets
    $stmt = $pdo->prepare("INSERT INTO census_sheets (ward, census_date, reported_by_name, reported_by_designation, reported_by_signature, figures_match)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE reported_by_name=VALUES(reported_by_name), reported_by_designation=VALUES(reported_by_designation), reported_by_signature=VALUES(reported_by_signature), figures_match=VALUES(figures_match)");
    $stmt->execute([
        $ward, $date,
        $data['reported_by_name'] ?? null,
        $data['reported_by_designation'] ?? null,
        $data['reported_by_signature'] ?? null,
        $data['figures_match'] ?? null
    ]);
    $census_sheet_id = $pdo->lastInsertId();
    if ($census_sheet_id == 0) {
        // If duplicate, fetch the existing id
        $stmt = $pdo->prepare("SELECT id FROM census_sheets WHERE ward=? AND census_date=?");
        $stmt->execute([$ward, $date]);
        $census_sheet_id = $stmt->fetchColumn();
    }

    // 2. Remove old details (for update)
    $tables = ['admissions','discharges','transfers_in','transfers_out','deaths','census_totals'];
    foreach ($tables as $table) {
        $pdo->prepare("DELETE FROM $table WHERE census_sheet_id=?")->execute([$census_sheet_id]);
    }

    // 3. Insert new details

    // Admissions
    foreach ((array)($data['admission_patient_name'] ?? []) as $i => $name) {
        if (empty($name)) continue;
        $pdo->prepare("INSERT INTO admissions (census_sheet_id, hospital_no, patient_name, age, sex, admission_diagnosis, treatment_plan, sponsors)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $census_sheet_id,
                is_array($data['admission_hospital_no'] ?? null) ? $data['admission_hospital_no'][$i] ?? null : $data['admission_hospital_no'] ?? null,
                $name,
                is_array($data['admission_age'] ?? null) ? $data['admission_age'][$i] ?? null : $data['admission_age'] ?? null,
                is_array($data['admission_sex'] ?? null) ? $data['admission_sex'][$i] ?? null : $data['admission_sex'] ?? null,
                is_array($data['admission_diagnosis'] ?? null) ? $data['admission_diagnosis'][$i] ?? null : $data['admission_diagnosis'] ?? null,
                is_array($data['admission_treatment'] ?? null) ? $data['admission_treatment'][$i] ?? null : $data['admission_treatment'] ?? null,
                is_array($data['admission_sponsors'] ?? null) ? $data['admission_sponsors'][$i] ?? null : $data['admission_sponsors'] ?? null
            ]);
    }

    // Discharges
    foreach ((array)($data['discharge_patient_name'] ?? []) as $i => $name) {
        if (empty($name)) continue;
        $pdo->prepare("INSERT INTO discharges (census_sheet_id, hospital_no, patient_name, age, sex, diagnosis, treatment_plan_after_discharge, in_patient_days, sponsors)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $census_sheet_id,
                is_array($data['discharge_hospital_no'] ?? null) ? $data['discharge_hospital_no'][$i] ?? null : $data['discharge_hospital_no'] ?? null,
                $name,
                is_array($data['discharge_age'] ?? null) ? $data['discharge_age'][$i] ?? null : $data['discharge_age'] ?? null,
                is_array($data['discharge_sex'] ?? null) ? $data['discharge_sex'][$i] ?? null : $data['discharge_sex'] ?? null,
                is_array($data['discharge_diagnosis'] ?? null) ? $data['discharge_diagnosis'][$i] ?? null : $data['discharge_diagnosis'] ?? null,
                is_array($data['discharge_treatment'] ?? null) ? $data['discharge_treatment'][$i] ?? null : $data['discharge_treatment'] ?? null,
                is_array($data['discharge_days'] ?? null) ? $data['discharge_days'][$i] ?? null : $data['discharge_days'] ?? null,
                is_array($data['discharge_sponsors'] ?? null) ? $data['discharge_sponsors'][$i] ?? null : $data['discharge_sponsors'] ?? null
            ]);
    }

    // Transfers In
    foreach ((array)($data['transferin_patient_name'] ?? []) as $i => $name) {
        if (empty($name)) continue;
        $pdo->prepare("INSERT INTO transfers_in (census_sheet_id, hospital_no, patient_name, age, sex, diagnosis, transferred_from)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE age=VALUES(age), sex=VALUES(sex), diagnosis=VALUES(diagnosis), transferred_from=VALUES(transferred_from)")
            ->execute([
                $census_sheet_id,
                is_array($data['transferin_hospital_no'] ?? null) ? $data['transferin_hospital_no'][$i] ?? null : $data['transferin_hospital_no'] ?? null,
                $name,
                is_array($data['transferin_age'] ?? null) ? $data['transferin_age'][$i] ?? null : $data['transferin_age'] ?? null,
                is_array($data['transferin_sex'] ?? null) ? $data['transferin_sex'][$i] ?? null : $data['transferin_sex'] ?? null,
                is_array($data['transferin_diagnosis'] ?? null) ? $data['transferin_diagnosis'][$i] ?? null : $data['transferin_diagnosis'] ?? null,
                is_array($data['transferin_from'] ?? null) ? $data['transferin_from'][$i] ?? null : $data['transferin_from'] ?? null
            ]);
    }

    // Transfers Out
    foreach ((array)($data['transferout_patient_name'] ?? []) as $i => $name) {
        if (empty($name)) continue;
        $pdo->prepare("INSERT INTO transfers_out (census_sheet_id, hospital_no, patient_name, age, sex, diagnosis, in_patient_days, transfer_to)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE age=VALUES(age), sex=VALUES(sex), diagnosis=VALUES(diagnosis), in_patient_days=VALUES(in_patient_days), transfer_to=VALUES(transfer_to)")
            ->execute([
                $census_sheet_id,
                is_array($data['transferout_hospital_no'] ?? null) ? $data['transferout_hospital_no'][$i] ?? null : $data['transferout_hospital_no'] ?? null,
                $name,
                is_array($data['transferout_age'] ?? null) ? $data['transferout_age'][$i] ?? null : $data['transferout_age'] ?? null,
                is_array($data['transferout_sex'] ?? null) ? $data['transferout_sex'][$i] ?? null : $data['transferout_sex'] ?? null,
                is_array($data['transferout_diagnosis'] ?? null) ? $data['transferout_diagnosis'][$i] ?? null : $data['transferout_diagnosis'] ?? null,
                is_array($data['transferout_days'] ?? null) ? $data['transferout_days'][$i] ?? null : $data['transferout_days'] ?? null,
                is_array($data['transferout_to'] ?? null) ? $data['transferout_to'][$i] ?? null : $data['transferout_to'] ?? null
            ]);
    }

    // Deaths
    foreach ((array)($data['death_patient_name'] ?? []) as $i => $name) {
        if (empty($name)) continue;
        $pdo->prepare("INSERT INTO deaths (census_sheet_id, hospital_no, patient_name, age, sex, cause_of_death, in_patient_days)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE age=VALUES(age), sex=VALUES(sex), cause_of_death=VALUES(cause_of_death), in_patient_days=VALUES(in_patient_days)")
            ->execute([
                $census_sheet_id,
                is_array($data['death_hospital_no'] ?? null) ? $data['death_hospital_no'][$i] ?? null : $data['death_hospital_no'] ?? null,
                $name,
                is_array($data['death_age'] ?? null) ? $data['death_age'][$i] ?? null : $data['death_age'] ?? null,
                is_array($data['death_sex'] ?? null) ? $data['death_sex'][$i] ?? null : $data['death_sex'] ?? null,
                is_array($data['death_cause'] ?? null) ? $data['death_cause'][$i] ?? null : $data['death_cause'] ?? null,
                is_array($data['death_days'] ?? null) ? $data['death_days'][$i] ?? null : $data['death_days'] ?? null
            ]);
    }

    // Census Totals (Add for Total and Subtract Total)
    $totals_map = [
        // Add for Total
        ['type' => 'add', 'label' => 'yesterday_bed_state', 'male' => 'add_yesterday_male', 'female' => 'add_yesterday_female', 'total' => 'add_yesterday_total'],
        ['type' => 'add', 'label' => 'admission_today', 'male' => 'add_admission_male', 'female' => 'add_admission_female', 'total' => 'add_admission_total'],
        ['type' => 'add', 'label' => 'transfer_in_today', 'male' => 'add_transferin_male', 'female' => 'add_transferin_female', 'total' => 'add_transferin_total'],
        ['type' => 'add', 'label' => 'total', 'male' => 'add_total_male', 'female' => 'add_total_female', 'total' => 'add_total_total'],
        // Subtract Total
        ['type' => 'subtract', 'label' => 'discharge_today', 'male' => 'sub_discharge_male', 'female' => 'sub_discharge_female', 'total' => 'sub_discharge_total'],
        ['type' => 'subtract', 'label' => 'transfer_out_today', 'male' => 'sub_transferout_male', 'female' => 'sub_transferout_female', 'total' => 'sub_transferout_total'],
        ['type' => 'subtract', 'label' => 'death_today', 'male' => 'sub_death_male', 'female' => 'sub_death_female', 'total' => 'sub_death_total'],
        ['type' => 'subtract', 'label' => 'total', 'male' => 'sub_total_male', 'female' => 'sub_total_female', 'total' => 'sub_total_total'],
    ];
    foreach ($totals_map as $row) {
        $male = isset($data[$row['male']]) ? intval($data[$row['male']]) : 0;
        $female = isset($data[$row['female']]) ? intval($data[$row['female']]) : 0;
        $total = isset($data[$row['total']]) ? intval($data[$row['total']]) : 0;
        if ($male !== 0 || $female !== 0 || $total !== 0) {
            $pdo->prepare("INSERT INTO census_totals (census_sheet_id, type, label, male, female, total)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE male=VALUES(male), female=VALUES(female), total=VALUES(total)")
                ->execute([
                    $census_sheet_id,
                    $row['type'],
                    $row['label'],
                    $male,
                    $female,
                    $total
                ]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Census saved']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ward = $_GET['ward'] ?? null;
    $date = $_GET['date'] ?? null;
    if (!$ward || !$date) {
        echo json_encode(['success' => false, 'message' => 'Ward and date required']);
        exit;
    }

    // Fetch census sheet
    $stmt = $pdo->prepare("SELECT * FROM census_sheets WHERE ward=? AND census_date=?");
    $stmt->execute([$ward, $date]);
    $sheet = $stmt->fetch();
    if (!$sheet) {
        echo json_encode(['success' => true, 'data' => null]);
        exit;
    }
    $census_sheet_id = $sheet['id'];

    // Fetch details
    $admissions = $pdo->prepare("SELECT * FROM admissions WHERE census_sheet_id=?");
    $admissions->execute([$census_sheet_id]);
    $discharges = $pdo->prepare("SELECT * FROM discharges WHERE census_sheet_id=?");
    $discharges->execute([$census_sheet_id]);
    $transfers_in = $pdo->prepare("SELECT * FROM transfers_in WHERE census_sheet_id=?");
    $transfers_in->execute([$census_sheet_id]);
    $transfers_out = $pdo->prepare("SELECT * FROM transfers_out WHERE census_sheet_id=?");
    $transfers_out->execute([$census_sheet_id]);
    $deaths = $pdo->prepare("SELECT * FROM deaths WHERE census_sheet_id=?");
    $deaths->execute([$census_sheet_id]);
    $totals = $pdo->prepare("SELECT * FROM census_totals WHERE census_sheet_id=?");
    $totals->execute([$census_sheet_id]);

    echo json_encode([
        'success' => true,
        'data' => [
            'sheet' => $sheet,
            'admissions' => $admissions->fetchAll(),
            'discharges' => $discharges->fetchAll(),
            'transfers_in' => $transfers_in->fetchAll(),
            'transfers_out' => $transfers_out->fetchAll(),
            'deaths' => $deaths->fetchAll(),
            'totals' => $totals->fetchAll(),
        ]
    ]);
    exit;
}

} catch (Exception $e) {
    file_put_contents('debug_error.txt', $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>