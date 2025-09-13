<?php
require_once 'config.php';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_employee':
                addEmployee($pdo, $_POST);
                break;
            case 'update_employee':
                updateEmployee($pdo, $_POST);
                break;
            case 'delete_employee':
                deleteEmployee($pdo, $_POST['id']);
                break;
            case 'delete_employees':
                deleteEmployees($pdo, $_POST['ids']);
                break;
            case 'import_employees':
                importEmployees($pdo, $_POST['employees']);
                break;
        }
    }
    exit;
}

// Fetch all employees
// Fetch all employees
$stmt = $pdo->query("
    SELECT 
        id,
        first_name_fr as firstNameFr,
        last_name_fr as lastNameFr,
        first_name_ar as firstNameAr,
        last_name_ar as lastNameAr,
        birth_date as birthDate,
        birth_place as birthPlace,
        recruitment_date as recruitmentDate,
        superior_position as superiorPosition,
        grade,
        grade_date as gradeDate,
        echelon,
        echelon_date as echelonDate,
        status,
        department,
        salary
    FROM employees 
    ORDER BY id
");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Functions for CRUD operations
function addEmployee($pdo, $data) {
    $sql = "INSERT INTO employees (first_name_fr, last_name_fr, first_name_ar, last_name_ar, birth_date, birth_place, recruitment_date, superior_position, grade, grade_date, echelon, echelon_date, status, department, salary) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['first_name_fr'], $data['last_name_fr'], $data['first_name_ar'], $data['last_name_ar'],
        $data['birth_date'], $data['birth_place'], $data['recruitment_date'], $data['superior_position'],
        $data['grade'], $data['grade_date'], $data['echelon'], $data['echelon_date'], $data['status'],
        $data['department'], $data['salary'] ?? 0
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
}

function updateEmployee($pdo, $data) {
    $sql = "UPDATE employees SET first_name_fr=?, last_name_fr=?, first_name_ar=?, last_name_ar=?, birth_date=?, 
            birth_place=?, recruitment_date=?, superior_position=?, grade=?, grade_date=?, echelon=?, echelon_date=?, 
            status=?, department=?, salary=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['first_name_fr'], $data['last_name_fr'], $data['first_name_ar'], $data['last_name_ar'],
        $data['birth_date'], $data['birth_place'], $data['recruitment_date'], $data['superior_position'],
        $data['grade'], $data['grade_date'], $data['echelon'], $data['echelon_date'], $data['status'],
        $data['department'], $data['salary'] ?? 0, $data['id']
    ]);
    echo json_encode(['success' => true]);
}

function deleteEmployee($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
}

function deleteEmployees($pdo, $ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "DELETE FROM employees WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    echo json_encode(['success' => true]);
}

function importEmployees($pdo, $employees) {
    $sql = "INSERT INTO employees (first_name_fr, last_name_fr, first_name_ar, last_name_ar, birth_date, birth_place, 
            recruitment_date, superior_position, grade, grade_date, echelon, echelon_date, status, department, salary) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($employees as $employee) {
        $stmt->execute([
            $employee['first_name_fr'], $employee['last_name_fr'], $employee['first_name_ar'], $employee['last_name_ar'],
            $employee['birth_date'], $employee['birth_place'], $employee['recruitment_date'], $employee['superior_position'],
            $employee['grade'], $employee['grade_date'], $employee['echelon'], $employee['echelon_date'], $employee['status'],
            $employee['department'], $employee['salary'] ?? 0
        ]);
    }
    echo json_encode(['success' => true, 'count' => count($employees)]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <base href="/">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gestionnaire d'Employés</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table.min.css">

    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table.min.js"></script>
    <!-- SheetJS library for Excel file reading -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/extensions/export/bootstrap-table-export.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table-locale-all.min.js"></script>

    <div class="container-fluid mt-3">
        <h1 class="text-center mb-4">Gestionnaire d'Employés</h1>
        
        <div class="select mb-3">
            <select id="locale" class="form-control">
                <option value="export">Exporter en PDF</option>
                <option value="fr-FR" selected>Français</option>
                <option value="ar-SA">العربية</option>
                <option value="en-US">English</option>
            </select>
        </div>

        <div id="toolbar" class="d-flex flex-wrap gap-2 align-items-center">
            <button id="importBtn" class="btn btn-success">
                <i class="fa fa-file-excel"></i> Importer Excel
            </button>
            <button id="addBtn" class="btn btn-primary">
                <i class="fa fa-plus"></i> Ajouter Employé
            </button>
            <button id="remove" class="btn btn-danger" disabled>
                <i class="fa fa-trash"></i> Supprimer
            </button>
            
            <div class="dropdown ms-2">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="printDropdown" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                    <i class="fa fa-print"></i> Imprimer Documents
                </button>
                <ul class="dropdown-menu" aria-labelledby="printDropdown">
                    <li><a class="dropdown-item print-option" href="#" data-doctype="attestation">Attestation de travail</a></li>
                    <li><a class="dropdown-item print-option" href="#" data-doctype="fiche-paie">Fiche de paie</a></li>
                    <li><a class="dropdown-item print-option" href="#" data-doctype="contrat">Contrat de travail</a></li>
                    <li><a class="dropdown-item print-option" href="#" data-doctype="certificat">Certificat de travail</a></li>
                    <li><a class="dropdown-item print-option" href="#" data-doctype="info-basic">Fiche informations</a></li>
                </ul>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Importer des données Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="excelFile" class="form-label">Sélectionner un fichier Excel</label>
                            <input type="file" class="form-control" id="excelFile" accept=".xlsx, .xls" required>
                            <div class="form-text">Veuillez sélectionner un fichier Excel avec les colonnes: ID, Prénom (FR), Nom (FR), Prénom (AR), Nom (AR), Date de naissance, Lieu de naissance, Date de recrutement, Poste supérieur, Grade, Date grade, Échelon, Date échelon, Statut, Département</div>
                        </div>
                        <div id="importPreview" class="mt-3" style="display: none;">
                            <h6>Aperçu des données importées:</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm" id="previewTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Prénom (FR)</th>
                                            <th>Nom (FR)</th>
                                            <th>Prénom (AR)</th>
                                            <th>Nom (AR)</th>
                                            <th>Date Naiss.</th>
                                            <th>Lieu Naiss.</th>
                                            <th>Date Recrut.</th>
                                            <th>Poste Sup.</th>
                                            <th>Grade</th>
                                            <th>Date Grade</th>
                                            <th>Échelon</th>
                                            <th>Date Échelon</th>
                                            <th>Statut</th>
                                            <th>Département</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="importData">Importer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Ajouter/Modifier Employé</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm">
                            <input type="hidden" id="editId">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editFirstNameFr" class="form-label">Prénom (Français)</label>
                                        <input type="text" class="form-control" id="editFirstNameFr" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editLastNameFr" class="form-label">Nom (Français)</label>
                                        <input type="text" class="form-control" id="editLastNameFr" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editFirstNameAr" class="form-label">Prénom (Arabe)</label>
                                        <input type="text" class="form-control" id="editFirstNameAr" required dir="rtl">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editLastNameAr" class="form-label">Nom (Arabe)</label>
                                        <input type="text" class="form-control" id="editLastNameAr" required dir="rtl">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editBirthDate" class="form-label">Date de naissance</label>
                                        <input type="date" class="form-control" id="editBirthDate" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editBirthPlace" class="form-label">Lieu de naissance</label>
                                        <input type="text" class="form-control" id="editBirthPlace" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editRecruitmentDate" class="form-label">Date de recrutement</label>
                                        <input type="date" class="form-control" id="editRecruitmentDate" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editSuperiorPosition" class="form-label">Poste supérieur</label>
                                        <input type="text" class="form-control" id="editSuperiorPosition">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editGrade" class="form-label">Grade</label>
                                        <input type="text" class="form-control" id="editGrade" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editGradeDate" class="form-label">Date du grade</label>
                                        <input type="date" class="form-control" id="editGradeDate" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editEchelon" class="form-label">Échelon</label>
                                        <input type="number" class="form-control" id="editEchelon" min="1" max="10" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editEchelonDate" class="form-label">Date échelon</label>
                                        <input type="date" class="form-control" id="editEchelonDate" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editStatus" class="form-label">Statut</label>
                                        <select class="form-control" id="editStatus" required>
                                            <option value="Actif">Actif</option>
                                            <option value="Inactif">Inactif</option>
                                            <option value="Congé">Congé</option>
                                            <option value="Retraité">Retraité</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editDepartment" class="form-label">Département</label>
                                        <input type="text" class="form-control" id="editDepartment" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editSalary" class="form-label">Salaire</label>
                                        <input type="number" class="form-control" id="editSalary" step="0.01" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="saveChanges">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Details Modal -->
        <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewModalLabel">Détails de l'Employé</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID:</strong> <span id="viewId"></span></p>
                                <p><strong>Prénom (FR):</strong> <span id="viewFirstNameFr"></span></p>
                                <p><strong>Nom (FR):</strong> <span id="viewLastNameFr"></span></p>
                                <p><strong>Prénom (AR):</strong> <span id="viewFirstNameAr" dir="rtl"></span></p>
                                <p><strong>Nom (AR):</strong> <span id="viewLastNameAr" dir="rtl"></span></p>
                                <p><strong>Date de naissance:</strong> <span id="viewBirthDate"></span></p>
                                <p><strong>Lieu de naissance:</strong> <span id="viewBirthPlace"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Date de recrutement:</strong> <span id="viewRecruitmentDate"></span></p>
                                <p><strong>Poste supérieur:</strong> <span id="viewSuperiorPosition"></span></p>
                                <p><strong>Grade:</strong> <span id="viewGrade"></span></p>
                                <p><strong>Date du grade:</strong> <span id="viewGradeDate"></span></p>
                                <p><strong>Échelon:</strong> <span id="viewEchelon"></span></p>
                                <p><strong>Date échelon:</strong> <span id="viewEchelonDate"></span></p>
                                <p><strong>Statut:</strong> <span id="viewStatus"></span></p>
                                <p><strong>Département:</strong> <span id="viewDepartment"></span></p>
                                <p><strong>Salaire:</strong> <span id="viewSalary"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <table
            id="table"
            data-toolbar="#toolbar"
            data-search="true"
            data-show-refresh="true"
            data-show-toggle="true"
            data-show-fullscreen="true"
            data-show-columns="true"
            data-show-columns-toggle-all="true"
            data-detail-view="false"
            data-show-export="true"
            data-click-to-select="true"
            data-minimum-count-columns="2"
            data-show-pagination-switch="true"
            data-pagination="true"
            data-id-field="id"
            data-page-list="[10, 25, 50, 100, all]"
            data-show-footer="true"
            data-side-pagination="client"
        >
        </table>
    </div>

    <script>
        const $table = $('#table')
        const $remove = $('#remove')
        const $printDropdown = $('#printDropdown')
        let selections = []

        function getIdSelections() {
            return $.map($table.bootstrapTable('getSelections'), function (row) {
                return row.id
            })
        }

        function actionsFormatter(value, row, index) {
            return [
                '<div class="btn-group" role="group">',
                '<button type="button" class="btn btn-sm btn-info view-btn" title="Voir">',
                '<i class="fa fa-eye"></i>',
                '</button>',
                '<button type="button" class="btn btn-sm btn-warning edit-btn" title="Modifier">',
                '<i class="fa fa-edit"></i>',
                '</button>',
                '<button type="button" class="btn btn-sm btn-danger delete-btn" title="Supprimer">',
                '<i class="fa fa-trash"></i>',
                '</button>',
                '</div>'
            ].join('')
        }

        // Function to print employee documents based on type
        function printEmployeeDocuments(docType) {
            const selectedEmployees = $table.bootstrapTable('getSelections');
            
            if (selectedEmployees.length === 0) {
                alert('Veuillez sélectionner au moins un employé à imprimer.');
                return;
            }
            
            // Create a print window
            const printWindow = window.open('', '_blank');
            
            // Build HTML content for printing based on document type
            let printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${getDocumentTitle(docType)}</title>
                    <meta charset="utf-8">
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                        .footer { text-align: center; margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 12px; }
                        .document-content { margin: 20px 0; }
                        .employee-info { margin-bottom: 25px; }
                        .section { margin-bottom: 15px; }
                        .section-title { font-weight: bold; border-bottom: 1px solid #eee; margin-bottom: 10px; padding-bottom: 5px; }
                        .row { display: flex; margin-bottom: 5px; }
                        .label { font-weight: bold; width: 200px; }
                        .arabic { direction: rtl; text-align: right; }
                        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; }
                        .signature-line { border-top: 1px solid #000; width: 250px; text-align: center; padding-top: 5px; }
                        .attestation-content { line-height: 1.6; text-align: justify; }
                        .salary-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        .salary-table th, .salary-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                        .salary-table th { background-color: #f2f2f2; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .mb-3 { margin-bottom: 15px; }
                        @media print {
                            body { margin: 0; padding: 15px; }
                            .header { border-bottom: 2px solid #000; }
                            .footer { border-top: 1px solid #ccc; }
                        }
                        @page {
                            size: A4;
                            margin: 1cm;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>${getDocumentTitle(docType)}</h1>
                        <p>Date d'émission: ${new Date().toLocaleDateString('fr-FR')}</p>
                    </div>
            `;
            
            // Add each employee's document
            selectedEmployees.forEach((employee, index) => {
                if (index > 0) {
                    printContent += `<div style="page-break-before: always;"></div>`;
                }
                
                printContent += `
                    <div class="document-content">
                        ${generateDocumentContent(docType, employee)}
                    </div>
                `;
            });
            
            printContent += `
                    <div class="footer">
                        <p>Document généré par le Système de Gestion des Employés - ${new Date().getFullYear()}</p>
                    </div>
                </body>
                </html>
            `;
            
            // Write content to print window
            printWindow.document.write(printContent);
            printWindow.document.close();
            
            // Print after a short delay to ensure content is loaded
            setTimeout(() => {
                printWindow.print();
                // printWindow.close(); // Uncomment to automatically close after printing
            }, 250);
        }

        // Helper function to get document title based on type
        function getDocumentTitle(docType) {
            const titles = {
                'attestation': 'Attestation de Travail',
                'fiche-paie': 'Fiche de Paie',
                'contrat': 'Contrat de Travail',
                'certificat': 'Certificat de Travail',
                'info-basic': 'Fiche Informations Employé'
            };
            return titles[docType] || 'Document Employé';
        }

        // Helper function to generate document content based on type
        function generateDocumentContent(docType, employee) {
            switch(docType) {
                case 'attestation':
                    return `
                        <div class="text-center mb-3">
                            <h2>ATTESTATION DE TRAVAIL</h2>
                        </div>
                        
                        <div class="attestation-content">
                            <p>Le soussigné, Responsable des Ressources Humaines de l'entreprise, atteste que :</p>
                            
                            <div class="employee-info">
                                <div class="row"><div class="label">Nom et Prénom :</div> <div>${employee.firstNameFr} ${employee.lastNameFr}</div></div>
                                <div class="row"><div class="label">الاسم و اللقب :</div> <div class="arabic">${employee.firstNameAr} ${employee.lastNameAr}</div></div>
                                <div class="row"><div class="label">Date de naissance :</div> <div>${employee.birthDate}</div></div>
                                <div class="row"><div class="label">Lieu de naissance :</div> <div>${employee.birthPlace}</div></div>
                                <div class="row"><div class="label">Numéro d'identification :</div> <div>${employee.id}</div></div>
                            </div>
                            
                            <p>Est employé(e) au sein de notre entreprise en qualité de <strong>${employee.grade}</strong> au département <strong>${employee.department}</strong> depuis le <strong>${employee.recruitmentDate}</strong>.</p>
                            
                            <p>Cette attestation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.</p>
                            
                            <p>Fait à Casablanca, le ${new Date().toLocaleDateString('fr-FR')}</p>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-line">Le Responsable RH</div>
                            <div class="signature-line">L'Employé</div>
                        </div>
                    `;
                    
                case 'fiche-paie':
                    // Use the actual salary from the database
                    const baseSalary = employee.salary || 15000;
                    const prime = Math.round(baseSalary * 0.15);
                    const cotisations = Math.round(baseSalary * 0.22);
                    const netSalary = baseSalary + prime - cotisations;
                    
                    return `
                        <div class="text-center mb-3">
                            <h2>FICHE DE PAIE</h2>
                            <p>Période: ${new Date().toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })}</p>
                        </div>
                        
                        <div class="employee-info">
                            <div class="row"><div class="label">Nom et Prénom :</div> <div>${employee.firstNameFr} ${employee.lastNameFr}</div></div>
                            <div class="row"><div class="label">Matricule :</div> <div>${employee.id}</div></div>
                            <div class="row"><div class="label">Département :</div> <div>${employee.department}</div></div>
                            <div class="row"><div class="label">Grade :</div> <div>${employee.grade}</div></div>
                            <div class="row"><div class="label">Échelon :</div> <div>${employee.echelon}</div></div>
                        </div>
                        
                        <table class="salary-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-right">Gains</th>
                                    <th class="text-right">Retenues</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Salaire de base</td>
                                    <td class="text-right">${baseSalary.toLocaleString('fr-FR')} DH</td>
                                    <td class="text-right"></td>
                                </tr>
                                <tr>
                                    <td>Prime d'ancienneté</td>
                                    <td class="text-right">${prime.toLocaleString('fr-FR')} DH</td>
                                    <td class="text-right"></td>
                                </tr>
                                <tr>
                                    <td>Cotisations sociales</td>
                                    <td class="text-right"></td>
                                    <td class="text-right">${cotisations.toLocaleString('fr-FR')} DH</td>
                                </tr>
                                <tr>
                                    <td>Impôt sur le revenu</td>
                                    <td class="text-right"></td>
                                    <td class="text-right">${Math.round(baseSalary * 0.1).toLocaleString('fr-FR')} DH</td>
                                </tr>
                                <tr style="font-weight: bold;">
                                    <td>NET À PAYER</td>
                                    <td class="text-right"></td>
                                    <td class="text-right">${netSalary.toLocaleString('fr-FR')} DH</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="signature-area">
                            <div class="signature-line">Le Responsable RH</div>
                            <div class="signature-line">L'Employé</div>
                        </div>
                    `;
                    
                case 'contrat':
                    return `
                        <div class="text-center mb-3">
                            <h2>CONTRAT DE TRAVAIL</h2>
                        </div>
                        
                        <div class="attestation-content">
                            <p>ENTRE L'ENTREPRISE,</p>
                            <p>Dûment représentée par le Directeur des Ressources Humaines,</p>
                            <p>ET</p>
                            
                            <div class="employee-info">
                                <div class="row"><div class="label">Nom et Prénom :</div> <div>${employee.firstNameFr} ${employee.lastNameFr}</div></div>
                                <div class="row"><div class="label">الاسم و اللقب :</div> <div class="arabic">${employee.firstNameAr} ${employee.lastNameAr}</div></div>
                                <div class="row"><div class="label">Né(e) le :</div> <div>${employee.birthDate}</div></div>
                                <div class="row"><div class="label">À :</div> <div>${employee.birthPlace}</div></div>
                            </div>
                            
                            <p>IL EST CONVENU CE QUI SUIT :</p>
                            
                            <div class="section">
                                <div class="section-title">Article 1 - Fonctions</div>
                                <p>Le salarié est engagé en qualité de ${employee.grade} au département ${employee.department}.</p>
                            </div>
                            
                            <div class="section">
                                <div class="section-title">Article 2 - Rémunération</div>
                                <p>Le salarié percevra une rémunération mensuelle selon les grilles de salaires en vigueur dans l'entreprise.</p>
                            </div>
                            
                            <div class="section">
                                <div class="section-title">Article 3 - Durée</div>
                                <p>Le présent contrat est conclu pour une durée indéterminée à compter du ${employee.recruitmentDate}.</p>
                            </div>
                            
                            <p>Fait à Casablanca, le ${new Date().toLocaleDateString('fr-FR')}</p>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-line">Pour l'Entreprise</div>
                            <div class="signature-line">Le Salarié</div>
                        </div>
                    `;
                    
                case 'certificat':
                    return `
                        <div class="text-center mb-3">
                            <h2>CERTIFICAT DE TRAVAIL</h2>
                        </div>
                        
                        <div class="attestation-content">
                            <p>Le soussigné, Responsable des Ressources Humaines, certifie que :</p>
                            
                            <div class="employee-info">
                                <div class="row"><div class="label">Mme/M. :</div> <div>${employee.firstNameFr} ${employee.lastNameFr}</div></div>
                                <div class="row"><div class="label">الاسم و اللقب :</div> <div class="arabic">${employee.firstNameAr} ${employee.lastNameAr}</div></div>
                                <div class="row"><div class="label">Né(e) le :</div> <div>${employee.birthDate}</div></div>
                                <div class="row"><div class="label">À :</div> <div>${employee.birthPlace}</div></div>
                            </div>
                            
                            <p>A été employé(e) par notre entreprise du ${employee.recruitmentDate} au ${new Date().toLocaleDateString('fr-FR')}.</p>
                            
                            <p>Il/Elle a occupé les fonctions de ${employee.grade} au département ${employee.department}.</p>
                            
                            <p>Pendant toute la durée de seuil parmi nous, nous avons apprécié ses compétences professionnelles et sa conduite irréprochable.</p>
                            
                            <p>Nous lui souhaitons plein succès dans ses nouvelles fonctions.</p>
                            
                            <p>Fait à Casablanca, le ${new Date().toLocaleDateString('fr-FR')}</p>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-line">Le Responsable RH</div>
                        </div>
                    `;
                    
                case 'info-basic':
                default:
                    return `
                        <div class="text-center mb-3">
                            <h2>FICHE INFORMATIONS EMPLOYÉ</h2>
                        </div>
                        
                        <div class="employee-info">
                            <div class="row"><div class="label">ID:</div> <div>${employee.id}</div></div>
                            <div class="row"><div class="label">Prénom (Français):</div> <div>${employee.firstNameFr}</div></div>
                            <div class="row"><div class="label">Nom (Français):</div> <div>${employee.lastNameFr}</div></div>
                            <div class="row"><div class="label">Prénom (Arabe):</div> <div class="arabic">${employee.firstNameAr}</div></div>
                            <div class="row"><div class="label">Nom (Arabe):</div> <div class="arabic">${employee.lastNameAr}</div></div>
                            <div class="row"><div class="label">Date de naissance:</div> <div>${employee.birthDate}</div></div>
                            <div class="row"><div class="label">Lieu de naissance:</div> <div>${employee.birthPlace}</div></div>
                            <div class="row"><div class="label">Date de recrutement:</div> <div>${employee.recruitmentDate}</div></div>
                            <div class="row"><div class="label">Poste supérieur:</div> <div>${employee.superiorPosition || 'N/A'}</div></div>
                            <div class="row"><div class="label">Grade:</div> <div>${employee.grade}</div></div>
                            <div class="row"><div class="label">Date du grade:</div> <div>${employee.gradeDate}</div></div>
                            <div class="row"><div class="label">Échelon:</div> <div>${employee.echelon}</div></div>
                            <div class="row"><div class="label">Date échelon:</div> <div>${employee.echelonDate}</div></div>
                            <div class="row"><div class="label">Statut:</div> <div>${employee.status}</div></div>
                            <div class="row"><div class="label">Département:</div> <div>${employee.department}</div></div>
                            <div class="row"><div class="label">Salaire:</div> <div>${employee.salary || 'N/A'} DH</div></div>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-line">Le Responsable RH</div>
                            <div class="signature-line">L'Employé</div>
                        </div>
                    `;
            }
        }

        function initTable() {
            $table.bootstrapTable('destroy').bootstrapTable({
                height: 550,
                locale: $('#locale').val(),
                columns: [
                    [
                        {
                            field: 'state',
                            checkbox: true,
                            rowspan: 2,
                            align: 'center',
                            valign: 'middle'
                        },
                        {
                            title: 'ID',
                            field: 'id',
                            rowspan: 2,
                            align: 'center',
                            valign: 'middle',
                            sortable: true,
                            footerFormatter: 'Total'
                        },
                        {
                            title: 'Informations Employé',
                            colspan: 15,
                            align: 'center'
                        }
                    ],
                    [
                        {
                            field: 'firstNameFr',
                            title: 'Prénom (FR)',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'lastNameFr',
                            title: 'Nom (FR)',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'firstNameAr',
                            title: 'Prénom (AR)',
                            sortable: true,
                            align: 'center',
                            cellStyle: {
                                css: {
                                    "direction": "rtl",
                                    "text-align": "right"
                                }
                            }
                        },
                        {
                            field: 'lastNameAr',
                            title: 'Nom (AR)',
                            sortable: true,
                            align: 'center',
                            cellStyle: {
                                css: {
                                    "direction": "rtl",
                                    "text-align": "right"
                                }
                            }
                        },
                        {
                            field: 'birthDate',
                            title: 'Date Naiss.',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'birthPlace',
                            title: 'Lieu Naiss.',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'recruitmentDate',
                            title: 'Date Recrut.',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'superiorPosition',
                            title: 'Poste Sup.',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'grade',
                            title: 'Grade',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'gradeDate',
                            title: 'Date Grade',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'echelon',
                            title: 'Échelon',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'echelonDate',
                            title: 'Date Échelon',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'status',
                            title: 'Statut',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'department',
                            title: 'Département',
                            sortable: true,
                            align: 'center'
                        },
                        {
                            field: 'salary',
                            title: 'Salaire',
                            sortable: true,
                            align: 'center',
                            formatter: function(value) {
                                return value ? value + ' DH' : 'N/A';
                            }
                        },
                        {
                            field: 'actions',
                            title: 'Actions',
                            align: 'center',
                            clickToSelect: false,
                            formatter: actionsFormatter,
                            events: {
                                'click .view-btn': function (e, value, row, index) {
                                    // Afficher les détails dans la modal
                                    $('#viewId').text(row.id);
                                    $('#viewFirstNameFr').text(row.firstNameFr);
                                    $('#viewLastNameFr').text(row.lastNameFr);
                                    $('#viewFirstNameAr').text(row.firstNameAr);
                                    $('#viewLastNameAr').text(row.lastNameAr);
                                    $('#viewBirthDate').text(row.birthDate);
                                    $('#viewBirthPlace').text(row.birthPlace);
                                    $('#viewRecruitmentDate').text(row.recruitmentDate);
                                    $('#viewSuperiorPosition').text(row.superiorPosition);
                                    $('#viewGrade').text(row.grade);
                                    $('#viewGradeDate').text(row.gradeDate);
                                    $('#viewEchelon').text(row.echelon);
                                    $('#viewEchelonDate').text(row.echelonDate);
                                    $('#viewStatus').text(row.status);
                                    $('#viewDepartment').text(row.department);
                                    $('#viewSalary').text(row.salary ? row.salary + ' DH' : 'N/A');
                                    
                                    $('#viewModal').modal('show');
                                },
                                'click .edit-btn': function (e, value, row, index) {
                                    // Remplir le formulaire d'édition
                                    $('#editModalLabel').text(`Modifier Employé #${row.id}`);
                                    $('#editId').val(row.id);
                                    $('#editFirstNameFr').val(row.firstNameFr);
                                    $('#editLastNameFr').val(row.lastNameFr);
                                    $('#editFirstNameAr').val(row.firstNameAr);
                                    $('#editLastNameAr').val(row.lastNameAr);
                                    $('#editBirthDate').val(row.birthDate);
                                    $('#editBirthPlace').val(row.birthPlace);
                                    $('#editRecruitmentDate').val(row.recruitmentDate);
                                    $('#editSuperiorPosition').val(row.superiorPosition);
                                    $('#editGrade').val(row.grade);
                                    $('#editGradeDate').val(row.gradeDate);
                                    $('#editEchelon').val(row.echelon);
                                    $('#editEchelonDate').val(row.echelonDate);
                                    $('#editStatus').val(row.status);
                                    $('#editDepartment').val(row.department);
                                    $('#editSalary').val(row.salary);
                                    
                                    $('#editModal').modal('show');
                                },
                                'click .delete-btn': function (e, value, row, index) {
                                    if (confirm('Êtes-vous sûr de vouloir supprimer cet employé ?')) {
                                        $.post('', {
                                            action: 'delete_employee',
                                            id: row.id
                                        }, function(response) {
                                            if (response.success) {
                                                $table.bootstrapTable('remove', {
                                                    field: 'id',
                                                    values: [row.id]
                                                });
                                                alert('Employé supprimé avec succès!');
                                            }
                                        }, 'json');
                                    }
                                }
                            }
                        }
                    ]
                ],
                data: <?php echo json_encode($employees); ?>
            });
            
            $table.on('check.bs.table uncheck.bs.table ' +
                'check-all.bs.table uncheck-all.bs.table',
            function () {
                const selectionsCount = $table.bootstrapTable('getSelections').length;
                $remove.prop('disabled', !selectionsCount);
                $printDropdown.prop('disabled', !selectionsCount);
                selections = getIdSelections();
            });
            
            $table.on('all.bs.table', function (e, name, args) {
                console.log(name, args);
            });
            
            $remove.click(function () {
                const ids = getIdSelections();
                if (confirm('Êtes-vous sûr de vouloir supprimer les employés sélectionnés ?')) {
                    $.post('', {
                        action: 'delete_employees',
                        ids: ids
                    }, function(response) {
                        if (response.success) {
                            $table.bootstrapTable('remove', {
                                field: 'id',
                                values: ids
                            });
                            $remove.prop('disabled', true);
                            $printDropdown.prop('disabled', true);
                            alert('Employés supprimés avec succès!');
                        }
                    }, 'json');
                }
            });
            
            // Handle print option click
            $('.print-option').click(function(e) {
                e.preventDefault();
                const docType = $(this).data('doctype');
                printEmployeeDocuments(docType);
            });
            
            // Handle add button click
            $('#addBtn').click(function() {
                $('#editModalLabel').text('Ajouter un Employé');
                $('#editForm')[0].reset();
                $('#editId').val('');
                $('#editModal').modal('show');
            });
            
            // Handle save changes button click
            $('#saveChanges').click(function() {
                const id = $('#editId').val();
                const formData = {
                    first_name_fr: $('#editFirstNameFr').val(),
                    last_name_fr: $('#editLastNameFr').val(),
                    first_name_ar: $('#editFirstNameAr').val(),
                    last_name_ar: $('#editLastNameAr').val(),
                    birth_date: $('#editBirthDate').val(),
                    birth_place: $('#editBirthPlace').val(),
                    recruitment_date: $('#editRecruitmentDate').val(),
                    superior_position: $('#editSuperiorPosition').val(),
                    grade: $('#editGrade').val(),
                    grade_date: $('#editGradeDate').val(),
                    echelon: parseInt($('#editEchelon').val()),
                    echelon_date: $('#editEchelonDate').val(),
                    status: $('#editStatus').val(),
                    department: $('#editDepartment').val(),
                    salary: parseFloat($('#editSalary').val()) || 0
                };
                
                if (id) {
                    // Editing existing row
                    formData.id = id;
                    formData.action = 'update_employee';
                    
                    $.post('', formData, function(response) {
                        if (response.success) {
                            $table.bootstrapTable('updateRow', {
                                index: $table.bootstrapTable('getRowByUniqueId', parseInt(id)),
                                row: {
                                    id: parseInt(id),
                                    firstNameFr: formData.first_name_fr,
                                    lastNameFr: formData.last_name_fr,
                                    firstNameAr: formData.first_name_ar,
                                    lastNameAr: formData.last_name_ar,
                                    birthDate: formData.birth_date,
                                    birthPlace: formData.birth_place,
                                    recruitmentDate: formData.recruitment_date,
                                    superiorPosition: formData.superior_position,
                                    grade: formData.grade,
                                    gradeDate: formData.grade_date,
                                    echelon: formData.echelon,
                                    echelonDate: formData.echelon_date,
                                    status: formData.status,
                                    department: formData.department,
                                    salary: formData.salary
                                }
                            });
                            $('#editModal').modal('hide');
                            alert('Employé modifié avec succès!');
                        }
                    }, 'json');
                } else {
                    // Adding new row
                    formData.action = 'add_employee';
                    
                    $.post('', formData, function(response) {
                        if (response.success) {
                            const newRow = {
                                id: response.id,
                                firstNameFr: formData.first_name_fr,
                                lastNameFr: formData.last_name_fr,
                                firstNameAr: formData.first_name_ar,
                                lastNameAr: formData.last_name_ar,
                                birthDate: formData.birth_date,
                                birthPlace: formData.birth_place,
                                recruitmentDate: formData.recruitment_date,
                                superiorPosition: formData.superior_position,
                                grade: formData.grade,
                                gradeDate: formData.grade_date,
                                echelon: formData.echelon,
                                echelonDate: formData.echelon_date,
                                status: formData.status,
                                department: formData.department,
                                salary: formData.salary
                            };
                            
                            $table.bootstrapTable('append', newRow);
                            $('#editModal').modal('hide');
                            alert('Employé ajouté avec succès!');
                        }
                    }, 'json');
                }
            });
            
            // Handle import button click
            $('#importBtn').click(function() {
                $('#importModal').modal('show');
                $('#excelFile').val('');
                $('#importPreview').hide();
                $('#previewTable tbody').empty();
            });
            
            // Handle file selection
            $('#excelFile').change(function(e) {
                const file = e.target.files[0];
                if (!file) return;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, { type: 'array' });
                        const firstSheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[firstSheetName];
                        const jsonData = XLSX.utils.sheet_to_json(worksheet);
                        
                        $('#previewTable tbody').empty();
                        
                        let previewData = [];
                        
                        jsonData.forEach(function(row, index) {
                            const previewRow = {
                                id: row.ID || row.id || index + 1,
                                firstNameFr: row['Prénom (FR)'] || row['firstNameFr'] || '',
                                lastNameFr: row['Nom (FR)'] || row['lastNameFr'] || '',
                                firstNameAr: row['Prénom (AR)'] || row['firstNameAr'] || 'غير معروف',
                                lastNameAr: row['Nom (AR)'] || row['lastNameAr'] || 'غير معروف',
                                birthDate: row['Date Naiss.'] || row['birthDate'] || '',
                                birthPlace: row['Lieu Naiss.'] || row['birthPlace'] || '',
                                recruitmentDate: row['Date Recrut.'] || row['recruitmentDate'] || '',
                                superiorPosition: row['Poste Sup.'] || row['superiorPosition'] || '',
                                grade: row.Grade || row.grade || '',
                                gradeDate: row['Date Grade'] || row['gradeDate'] || '',
                                echelon: row.Échelon || row.echelon || 1,
                                echelonDate: row['Date Échelon'] || row['echelonDate'] || '',
                                status: row.Statut || row.status || 'Actif',
                                department: row.Département || row.department || '',
                                salary: row.Salaire || row.salary || 0
                            };
                            
                            previewData.push(previewRow);
                            
                            $('#previewTable tbody').append(`
                                <tr>
                                    <td>${previewRow.id}</td>
                                    <td>${previewRow.firstNameFr}</td>
                                    <td>${previewRow.lastNameFr}</td>
                                    <td dir="rtl">${previewRow.firstNameAr}</td>
                                    <td dir="rtl">${previewRow.lastNameAr}</td>
                                    <td>${previewRow.birthDate}</td>
                                    <td>${previewRow.birthPlace}</td>
                                    <td>${previewRow.recruitmentDate}</td>
                                    <td>${previewRow.superiorPosition}</td>
                                    <td>${previewRow.grade}</td>
                                    <td>${previewRow.gradeDate}</td>
                                    <td>${previewRow.echelon}</td>
                                    <td>${previewRow.echelonDate}</td>
                                    <td>${previewRow.status}</td>
                                    <td>${previewRow.department}</td>
                                </tr>
                            `);
                        });
                        
                        $('#importPreview').data('importData', previewData);
                        $('#importPreview').show();
                        
                    } catch (error) {
                        alert('Erreur de lecture du fichier Excel: ' + error.message);
                    }
                };
                
                reader.readAsArrayBuffer(file);
            });
            
            // Handle import data button click
            $('#importData').click(function() {
                const importData = $('#importPreview').data('importData');
                
                if (!importData || importData.length === 0) {
                    alert('Aucune donnée à importer. Veuillez sélectionner un fichier Excel d\'abord.');
                    return;
                }
                
                $.post('', {
                    action: 'import_employees',
                    employees: importData
                }, function(response) {
                    if (response.success) {
                        // Reload the table data
                        location.reload();
                    }
                }, 'json');
            });
        }

        $(function() {
            initTable();

            $('#locale').change(function() {
                if ($(this).val() === 'export') {
                    // Export functionality would go here
                    alert('Fonctionnalité d\'exportation PDF à implémenter');
                } else {
                    initTable();
                }
            });
        });
    </script>

    <style>
        .select,
        #locale {
            width: 100%;
        }
        .like {
            margin-right: 10px;
        }
        body {
            background-color: #f8f9fa;
        }
        h1 {
            color: #0d6efd;
        }
        #toolbar {
            margin-bottom: 15px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .modal-header {
            background-color: #f8f9fa;
        }
        .table th {
            background-color: #0d6efd;
            color: white;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .btn-group .btn {
            margin-right: 2px;
        }
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        .dropdown-menu {
            z-index: 1000;
        }
    </style>
</body>
</html>