<template>
    <BusinessLayout>
        <Head title="Bulk Upload Staff" />

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
            <Link href="/business/staff" class="text-blue-600 hover:underline">&larr; Back to Staff</Link>
            <h1 class="text-3xl font-bold text-gray-900 mt-4">Bulk Upload Staff</h1>
            <p class="text-gray-600 mt-2">Upload a CSV or Excel file with your staff data. The system will automatically map the columns and let you review before importing.</p>

            <!-- Step Indicator -->
            <div class="flex items-center justify-center mt-8 mb-10 gap-0">
                <div v-for="(stepLabel, i) in ['Upload File', 'Map Columns', 'Review & Import']" :key="i" class="flex items-center">
                    <div class="flex items-center gap-2">
                        <div :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                            step > i + 1 ? 'bg-green-600 text-white' :
                            step === i + 1 ? 'bg-blue-600 text-white' :
                            'bg-gray-200 text-gray-500'
                        ]">
                            <span v-if="step > i + 1">&#10003;</span>
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span :class="step === i + 1 ? 'text-blue-700 font-semibold' : 'text-gray-500'" class="text-sm hidden sm:inline">{{ stepLabel }}</span>
                    </div>
                    <div v-if="i < 2" class="w-8 sm:w-16 h-0.5 mx-2" :class="step > i + 1 ? 'bg-green-400' : 'bg-gray-300'"></div>
                </div>
            </div>

            <!-- STEP 1: Upload -->
            <div v-if="step === 1" class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                    <h2 class="text-xl font-semibold text-gray-800">Upload Your File</h2>
                    <a href="/business/staff/download-template" class="inline-flex items-center gap-2 text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 px-4 py-2 rounded-lg text-sm font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Download Template
                    </a>
                </div>

                <!-- Drag & Drop Zone -->
                <div
                    @dragover.prevent="isDragOver = true"
                    @dragleave="isDragOver = false"
                    @drop.prevent="handleDrop"
                    @click="$refs.fileInput.click()"
                    :class="[
                        'border-2 border-dashed rounded-xl p-12 text-center cursor-pointer transition-all duration-200',
                        isDragOver ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50'
                    ]"
                >
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="mt-4 text-lg font-medium text-gray-700">Drag & drop your file here</p>
                    <p class="text-gray-500 mt-1">or click to browse</p>
                    <p class="text-gray-400 text-sm mt-3">Supports CSV and Excel (.csv, .xlsx, .xls)</p>
                    <input ref="fileInput" type="file" accept=".csv,.xlsx,.xls" class="hidden" @change="handleFileSelect" />
                </div>

                <!-- Selected File Info -->
                <div v-if="fileName" class="mt-4 flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-blue-800 truncate">{{ fileName }}</p>
                        <p class="text-sm text-blue-600">{{ parsedRows.length }} rows found</p>
                    </div>
                    <button @click.stop="resetUpload" class="text-blue-600 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Parse Error -->
                <div v-if="parseError" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
                    {{ parseError }}
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        @click="goToMapping"
                        :disabled="!parsedRows.length"
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-6 py-2.5 rounded-lg font-medium transition"
                    >
                        Next: Map Columns &rarr;
                    </button>
                </div>
            </div>

            <!-- STEP 2: Column Mapping -->
            <div v-if="step === 2" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Map Your Columns</h2>
                <p class="text-gray-500 text-sm mb-6">We've auto-detected your column mappings. Adjust them if needed.</p>

                <div v-if="isMappingLoading" class="flex items-center justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="text-gray-600">AI is analyzing your columns...</span>
                </div>

                <div v-else>
                    <div class="space-y-3">
                        <div v-for="header in fileHeaders" :key="header" class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 bg-gray-50 rounded-lg p-3">
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-gray-800 text-sm">{{ header }}</span>
                                <span v-if="sampleValues[header]" class="text-gray-400 text-xs ml-2 truncate">(e.g. {{ sampleValues[header] }})</span>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                <select v-model="columnMapping[header]" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-full sm:w-56">
                                    <option value="skip">-- Skip this column --</option>
                                    <option value="full_name">Full Name (will split)</option>
                                    <option value="first_name">First Name *</option>
                                    <option value="last_name">Last Name *</option>
                                    <option value="email">Email *</option>
                                    <option value="phone">Phone</option>
                                    <option value="tax_identification_number">TIN</option>
                                    <option value="monthly_salary">Monthly Salary *</option>
                                    <option value="employment_type">Employment Type *</option>
                                    <option value="designation">Designation *</option>
                                    <option value="date_employed">Date Employed *</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Mapping Validation -->
                    <div v-if="mappingErrors.length" class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="font-medium text-yellow-800 mb-1">Missing required fields:</p>
                        <ul class="list-disc list-inside text-yellow-700 text-sm">
                            <li v-for="err in mappingErrors" :key="err">{{ err }}</li>
                        </ul>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <button @click="step = 1" class="text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                            &larr; Back
                        </button>
                        <button
                            @click="applyMapping"
                            :disabled="mappingErrors.length > 0"
                            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-6 py-2.5 rounded-lg font-medium transition"
                        >
                            Next: Review Data &rarr;
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Review & Import -->
            <div v-if="step === 3" class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Review Staff Data</h2>
                        <p class="text-gray-500 text-sm mt-1">{{ mappedStaff.length }} staff members ready to import. You can edit any cell before importing.</p>
                    </div>
                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                        {{ validRowCount }}/{{ mappedStaff.length }} valid
                    </span>
                </div>

                <!-- Editable Preview Table -->
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 w-8">#</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">First Name</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Last Name</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Email</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Phone</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">TIN</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Salary</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Type</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Designation</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Date Employed</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 w-10">Status</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in mappedStaff" :key="i" :class="[rowValidation[i]?.valid ? 'hover:bg-gray-50' : 'bg-red-50']" class="border-b">
                                <td class="py-2 px-3 text-gray-400">{{ i + 1 }}</td>
                                <td class="py-1 px-1">
                                    <input v-model="row.first_name" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="First name" />
                                </td>
                                <td class="py-1 px-1">
                                    <input v-model="row.last_name" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="Last name" />
                                </td>
                                <td class="py-1 px-1">
                                    <input v-model="row.email" type="email" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="email" />
                                </td>
                                <td class="py-1 px-1">
                                    <input v-model="row.phone" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="Phone" />
                                </td>
                                <td class="py-1 px-1">
                                    <input v-model="row.tax_identification_number" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="TIN" />
                                </td>
                                <td class="py-1 px-1">
                                    <input v-model="row.monthly_salary" type="number" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="Salary" />
                                </td>
                                <td class="py-1 px-1">
                                    <select v-model="row.employment_type" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                        <option value="">Select</option>
                                        <option value="full_time">Full Time</option>
                                        <option value="part_time">Part Time</option>
                                        <option value="contract">Contract</option>
                                    </select>
                                </td>
                                <td class="py-1 px-1">
                                    <input v-model="row.designation" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="Designation" />
                                </td>
                                <td class="py-1 px-1">
                                    <input v-model="row.date_employed" type="date" class="w-full px-2 py-1 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none" />
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <span v-if="rowValidation[i]?.valid" class="text-green-600" title="Valid">&#10003;</span>
                                    <span v-else class="text-red-500 cursor-help" :title="rowValidation[i]?.errors?.join(', ')">&#10007;</span>
                                </td>
                                <td class="py-2 px-2">
                                    <button @click="removeRow(i)" class="text-gray-400 hover:text-red-600" title="Remove row">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Validation Summary -->
                <div v-if="invalidRowCount > 0" class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800 font-medium">{{ invalidRowCount }} row(s) have validation errors. Hover on the &#10007; icon to see details. Fix them or remove those rows before importing.</p>
                </div>

                <!-- Import Results -->
                <div v-if="importResults" class="mt-4 rounded-lg p-4" :class="importResults.results.failed > 0 ? 'bg-yellow-50 border border-yellow-200' : 'bg-green-50 border border-green-200'">
                    <p class="font-medium" :class="importResults.results.failed > 0 ? 'text-yellow-800' : 'text-green-800'">{{ importResults.message }}</p>
                    <ul v-if="importResults.results.errors?.length" class="mt-2 list-disc list-inside text-sm text-yellow-700">
                        <li v-for="(err, i) in importResults.results.errors" :key="i">{{ err }}</li>
                    </ul>
                </div>

                <div class="mt-6 flex justify-between">
                    <button @click="step = 2" class="text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                        &larr; Back to Mapping
                    </button>
                    <button
                        @click="submitImport"
                        :disabled="isImporting || validRowCount === 0"
                        class="bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-6 py-2.5 rounded-lg font-medium transition flex items-center gap-2"
                    >
                        <svg v-if="isImporting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ isImporting ? 'Importing...' : `Import ${validRowCount} Staff Members` }}
                    </button>
                </div>
            </div>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BusinessLayout from '@/Layouts/BusinessLayout.vue';
import Papa from 'papaparse';
import * as XLSX from 'xlsx';

// State
const step = ref(1);
const fileName = ref('');
const isDragOver = ref(false);
const parseError = ref('');
const fileHeaders = ref([]);
const parsedRows = ref([]);
const columnMapping = ref({});
const mappedStaff = ref([]);
const sampleValues = ref({});
const isMappingLoading = ref(false);
const isImporting = ref(false);
const importResults = ref(null);

// File handling
function handleDrop(e) {
    isDragOver.value = false;
    const file = e.dataTransfer.files[0];
    if (file) processFile(file);
}

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) processFile(file);
}

function processFile(file) {
    parseError.value = '';
    const ext = file.name.split('.').pop().toLowerCase();

    if (!['csv', 'xlsx', 'xls'].includes(ext)) {
        parseError.value = 'Unsupported file type. Please upload a CSV or Excel file (.csv, .xlsx, .xls).';
        return;
    }

    fileName.value = file.name;

    if (ext === 'csv') {
        parseCSV(file);
    } else {
        parseExcel(file);
    }
}

function parseCSV(file) {
    Papa.parse(file, {
        header: true,
        skipEmptyLines: true,
        complete: (results) => {
            if (results.errors.length > 0 && results.data.length === 0) {
                parseError.value = 'Failed to parse CSV: ' + results.errors[0].message;
                return;
            }
            fileHeaders.value = results.meta.fields || [];
            parsedRows.value = results.data;
            buildSampleValues();
        },
        error: (err) => {
            parseError.value = 'Failed to read CSV file: ' + err.message;
        }
    });
}

function parseExcel(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheet];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { defval: '' });

            if (jsonData.length === 0) {
                parseError.value = 'The file appears to be empty. Please check your data.';
                return;
            }

            fileHeaders.value = Object.keys(jsonData[0]);
            parsedRows.value = jsonData;
            buildSampleValues();
        } catch (err) {
            parseError.value = 'Failed to read Excel file: ' + err.message;
        }
    };
    reader.readAsArrayBuffer(file);
}

function buildSampleValues() {
    sampleValues.value = {};
    for (const header of fileHeaders.value) {
        const firstVal = parsedRows.value.find(row => row[header] && String(row[header]).trim());
        sampleValues.value[header] = firstVal ? String(firstVal[header]).substring(0, 40) : '';
    }
}

function resetUpload() {
    fileName.value = '';
    fileHeaders.value = [];
    parsedRows.value = [];
    columnMapping.value = {};
    mappedStaff.value = [];
    parseError.value = '';
    importResults.value = null;
}

// Column mapping step
async function goToMapping() {
    if (!parsedRows.value.length) return;
    step.value = 2;
    isMappingLoading.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/business/staff/bulk-upload/map-columns', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                headers: fileHeaders.value,
                sample_rows: parsedRows.value.slice(0, 3),
            }),
        });

        if (response.ok) {
            const data = await response.json();
            columnMapping.value = data.mapping || {};
        } else {
            // Fallback if API fails—use empty mapping
            for (const h of fileHeaders.value) {
                columnMapping.value[h] = 'skip';
            }
        }
    } catch (err) {
        // Offline fallback
        for (const h of fileHeaders.value) {
            columnMapping.value[h] = 'skip';
        }
    } finally {
        isMappingLoading.value = false;
    }
}

// Mapping validation
const requiredFields = ['first_name', 'last_name', 'email', 'monthly_salary', 'employment_type', 'designation', 'date_employed'];
const mappingErrors = computed(() => {
    const errors = [];
    const mapped = Object.values(columnMapping.value);

    // If full_name is mapped, first_name and last_name can be skipped
    const hasFullName = mapped.includes('full_name');

    for (const field of requiredFields) {
        if (field === 'first_name' && hasFullName) continue;
        if (field === 'last_name' && hasFullName) continue;
        if (!mapped.includes(field)) {
            const label = {
                first_name: 'First Name',
                last_name: 'Last Name',
                email: 'Email',
                monthly_salary: 'Monthly Salary',
                employment_type: 'Employment Type',
                designation: 'Designation',
                date_employed: 'Date Employed',
            }[field];
            errors.push(`${label} is required but not mapped to any column`);
        }
    }
    return errors;
});

// Apply mapping to transform rows
function applyMapping() {
    const mapped = [];

    for (const row of parsedRows.value) {
        const staffRow = {
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            tax_identification_number: '',
            monthly_salary: '',
            employment_type: '',
            designation: '',
            date_employed: '',
        };

        for (const [header, field] of Object.entries(columnMapping.value)) {
            if (field === 'skip') continue;

            const rawValue = String(row[header] ?? '').trim();

            if (field === 'full_name') {
                // Split full name into first and last
                const parts = rawValue.split(/\s+/);
                staffRow.first_name = parts[0] || '';
                staffRow.last_name = parts.slice(1).join(' ') || '';
            } else if (field === 'monthly_salary') {
                // Clean salary value (remove commas, currency symbols)
                staffRow.monthly_salary = rawValue.replace(/[^0-9.]/g, '');
            } else if (field === 'employment_type') {
                // Normalize employment type
                staffRow.employment_type = normalizeEmploymentType(rawValue);
            } else if (field === 'date_employed') {
                // Try to normalize date
                staffRow.date_employed = normalizeDate(rawValue);
            } else {
                staffRow[field] = rawValue;
            }
        }

        // Skip completely empty rows
        if (!staffRow.first_name && !staffRow.last_name && !staffRow.email) continue;

        mapped.push(staffRow);
    }

    mappedStaff.value = mapped;
    step.value = 3;
}

function normalizeEmploymentType(val) {
    const lower = val.toLowerCase().replace(/[\s_-]/g, '');
    if (lower.includes('fulltime') || lower === 'full' || lower === 'ft' || lower === 'permanent') return 'full_time';
    if (lower.includes('parttime') || lower === 'part' || lower === 'pt') return 'part_time';
    if (lower.includes('contract') || lower === 'temp' || lower === 'temporary' || lower === 'freelance') return 'contract';
    return val;
}

function normalizeDate(val) {
    if (!val) return '';

    // If it's a number (Excel serial date)
    if (!isNaN(val) && Number(val) > 10000) {
        const excelEpoch = new Date(1899, 11, 30);
        const date = new Date(excelEpoch.getTime() + Number(val) * 86400000);
        return date.toISOString().split('T')[0];
    }

    // Try common date formats
    const d = new Date(val);
    if (!isNaN(d.getTime())) {
        return d.toISOString().split('T')[0];
    }

    // Try DD/MM/YYYY or DD-MM-YYYY
    const parts = val.split(/[\/\-\.]/);
    if (parts.length === 3) {
        const [a, b, c] = parts;
        if (c.length === 4) {
            // DD/MM/YYYY
            const tryDate = new Date(`${c}-${b.padStart(2, '0')}-${a.padStart(2, '0')}`);
            if (!isNaN(tryDate.getTime())) return tryDate.toISOString().split('T')[0];
        }
    }

    return val;
}

// Row validation
const rowValidation = computed(() => {
    return mappedStaff.value.map((row) => {
        const errors = [];
        if (!row.first_name) errors.push('First name is required');
        if (!row.last_name) errors.push('Last name is required');
        if (!row.email) errors.push('Email is required');
        if (row.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(row.email)) errors.push('Invalid email');
        if (!row.monthly_salary || isNaN(row.monthly_salary) || Number(row.monthly_salary) < 0) errors.push('Valid salary is required');
        if (!['full_time', 'part_time', 'contract'].includes(row.employment_type)) errors.push('Employment type must be full_time, part_time, or contract');
        if (!row.designation) errors.push('Designation is required');
        if (!row.date_employed) errors.push('Date employed is required');

        return { valid: errors.length === 0, errors };
    });
});

const validRowCount = computed(() => rowValidation.value.filter(v => v.valid).length);
const invalidRowCount = computed(() => rowValidation.value.filter(v => !v.valid).length);

function removeRow(index) {
    mappedStaff.value.splice(index, 1);
}

// Submit import
async function submitImport() {
    if (validRowCount.value === 0) return;

    isImporting.value = true;
    importResults.value = null;

    // Only send valid rows
    const validRows = mappedStaff.value.filter((_, i) => rowValidation.value[i]?.valid);

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/business/staff/bulk-upload', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ staff_data: validRows }),
        });

        const data = await response.json();
        importResults.value = data;

        if (data.results?.success > 0 && data.results?.failed === 0) {
            // Full success - redirect after a brief delay
            setTimeout(() => {
                router.visit('/business/staff', {
                    preserveScroll: false,
                });
            }, 2000);
        }
    } catch (err) {
        importResults.value = {
            message: 'Import failed. Please try again.',
            results: { success: 0, failed: mappedStaff.value.length, errors: [err.message] },
        };
    } finally {
        isImporting.value = false;
    }
}
</script>
