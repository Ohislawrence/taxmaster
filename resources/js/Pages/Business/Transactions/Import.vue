<template>
  <BusinessLayout>
    <Head title="Import Transactions" />

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
      <!-- Header Section -->
      <div class="mb-8">
        <Link
          href="/business/transactions"
          class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-900 transition-colors group mb-4"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Transactions
        </Link>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">Import Transactions</h1>
            <p class="text-slate-500 mt-2 max-w-2xl">Upload CSV or Excel files and use AI to map columns to transaction fields. Review and edit before importing.</p>
          </div>

          <!-- Quick Stats (when data is loaded) -->
          <div v-if="parsedRows.length > 0 && step === 3" class="flex gap-3">
            <div class="bg-green-50 rounded-xl px-4 py-2 text-center">
              <div class="text-2xl font-bold text-green-600">{{ validRowCount }}</div>
              <div class="text-xs text-green-600">Valid</div>
            </div>
            <div v-if="invalidRowCount > 0" class="bg-red-50 rounded-xl px-4 py-2 text-center">
              <div class="text-2xl font-bold text-red-600">{{ invalidRowCount }}</div>
              <div class="text-xs text-red-600">Invalid</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step Indicator - Enhanced -->
      <div class="mb-8">
        <div class="flex items-center justify-between max-w-2xl mx-auto">
          <div v-for="(stepInfo, i) in steps" :key="i" class="flex-1 relative">
            <div class="flex flex-col items-center">
              <div
                :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300',
                  step > i + 1 ? 'bg-green-600 text-white' :
                  step === i + 1 ? 'bg-blue-600 text-white ring-4 ring-blue-100' :
                  'bg-slate-200 text-slate-500'
                ]"
              >
                <svg v-if="step > i + 1" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span v-else>{{ i + 1 }}</span>
              </div>
              <span class="text-xs font-medium mt-2" :class="step === i + 1 ? 'text-blue-600' : 'text-slate-500'">
                {{ stepInfo.label }}
              </span>
            </div>
            <div
              v-if="i < steps.length - 1"
              class="absolute top-5 left-1/2 w-full h-0.5 -translate-y-1/2"
              :class="step > i + 1 ? 'bg-green-400' : 'bg-slate-200'"
              style="transform: translateX(0%);"
            ></div>
          </div>
        </div>
      </div>

      <!-- STEP 1: Upload -->
      <div v-if="step === 1" class="bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-sm">
        <div class="max-w-xl mx-auto">
          <!-- Drag & Drop Zone -->
          <div
            class="border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer hover:border-blue-400 hover:bg-blue-50/30"
            :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-slate-200'"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
          >
            <input
              ref="fileInput"
              type="file"
              accept=".csv,.xlsx,.xls,.ods"
              @change="onFileChange"
              class="hidden"
            />

            <svg class="w-12 h-12 mx-auto text-slate-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>

            <p class="text-slate-600 mb-2">Drag and drop your file here, or <span class="text-blue-600 font-medium">browse</span></p>
            <p class="text-xs text-slate-400">Supports CSV, XLSX, XLS, ODS (max 10MB)</p>
          </div>

          <!-- Selected File Preview -->
          <div v-if="fileName" class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
              <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-blue-900 truncate">{{ fileName }}</p>
                <p class="text-sm text-blue-600">{{ parsedRows.length.toLocaleString() }} rows found</p>
              </div>
              <button @click="clear" class="text-slate-400 hover:text-red-500 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="parseError" class="mt-4 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm text-red-700">{{ parseError }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-6 flex justify-end">
            <button
              @click="goToMapping"
              :disabled="!parsedRows.length"
              class="group inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white px-6 py-2.5 rounded-xl font-medium transition-all"
            >
              Next: Map Columns
              <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- STEP 2: Mapping -->
      <div v-if="step === 2" class="bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
          <div>
            <h2 class="text-xl font-semibold text-slate-900">Map Your Columns</h2>
            <p class="text-sm text-slate-500 mt-1">We've auto-detected your column mappings. Adjust them if needed.</p>
          </div>
          <div class="flex gap-2">
            <button @click="applyAllSuggestions" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
              Apply Suggestions
            </button>
            <button @click="refreshSuggestions" class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
              Refresh
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="isMappingLoading" class="flex flex-col items-center justify-center py-16">
          <div class="relative">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
            </div>
          </div>
          <p class="text-slate-500 mt-4">AI is analyzing your columns...</p>
          <p class="text-xs text-slate-400 mt-1">This may take a few seconds</p>
        </div>

        <!-- Mapping Interface -->
        <div v-else>
          <div class="space-y-3 max-h-[500px] overflow-y-auto">
            <div
              v-for="header in headers"
              :key="header"
              class="bg-slate-50 rounded-xl p-4 hover:bg-slate-100 transition-colors"
            >
              <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-slate-900 text-sm">{{ header }}</span>
                    <span class="text-xs text-slate-400 bg-white px-2 py-0.5 rounded">column</span>
                  </div>
                  <p class="text-xs text-slate-400 mt-1 truncate">Example: {{ sampleRows[0]?.[header] || '—' }}</p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                  <select
                    v-model="mapping[header]"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-white w-full sm:w-56"
                  >
                    <option value="ignore">— Skip this column —</option>
                    <option value="transaction_date">📅 Transaction Date</option>
                    <option value="amount">💰 Amount</option>
                    <option value="description">📝 Description</option>
                    <option value="reference">🔖 Reference</option>
                    <option value="counterparty">👤 Counterparty</option>
                    <option value="balance">⚖️ Balance</option>
                    <option value="currency">💱 Currency</option>
                    <option value="type">🏷️ Type</option>
                    <option value="category">📂 Category</option>
                  </select>

                  <div v-if="suggestions[header]" class="text-xs bg-white rounded-lg px-2 py-1 whitespace-nowrap">
                    <span class="text-slate-500">Suggested:</span>
                    <span class="font-medium text-slate-700 ml-1">{{ getFieldLabel(suggestions[header]) }}</span>
                    <button @click="applySuggestion(header)" class="ml-2 text-blue-600 hover:text-blue-700 font-medium">
                      Apply
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Validation Errors -->
          <div v-if="mappingErrors.length" class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <div>
                <p class="font-medium text-amber-800">Missing required fields:</p>
                <ul class="list-disc list-inside text-sm text-amber-700 mt-1">
                  <li v-for="err in mappingErrors" :key="err">{{ err }}</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-6 flex flex-col sm:flex-row justify-between gap-3 pt-4 border-t border-slate-100">
            <button @click="step = 1" class="text-slate-600 hover:text-slate-800 px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
              ← Back
            </button>
            <div class="flex gap-3">
              <button @click="autoMap" :disabled="isMappingLoading" class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                Auto-map (AI)
              </button>
              <button
                @click="applyMapping"
                :disabled="mappingErrors.length > 0"
                class="group inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white px-6 py-2.5 rounded-xl font-medium transition-all"
              >
                Next: Review Data
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- STEP 3: Review & Import -->
      <div v-if="step === 3" class="bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
          <div>
            <h2 class="text-xl font-semibold text-slate-900">Review Transactions</h2>
            <p class="text-sm text-slate-500 mt-1">{{ mappedRows.length.toLocaleString() }} transactions ready. Click any cell to edit before importing.</p>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <!-- Bank Account Selector -->
            <div class="relative">
              <label class="text-xs text-slate-500 block mb-1">Bank Account</label>
              <select v-model="selectedBankAccountId" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-white">
                <option value="new">+ Create new bank account...</option>
                <option v-for="acct in bankAccounts" :key="acct.id" :value="acct.id">
                  {{ acct.bank_name }} — {{ acct.account_number }}
                </option>
              </select>
            </div>

            <div class="text-right">
              <div class="text-2xl font-bold text-slate-900">{{ validRowCount }}</div>
              <div class="text-xs text-slate-500">valid transactions</div>
            </div>
          </div>
        </div>

        <!-- New Bank Account Form -->
        <div v-if="selectedBankAccountId === 'new'" class="mb-6 bg-slate-50 rounded-xl p-4">
          <h3 class="text-sm font-semibold text-slate-900 mb-3">New Bank Account Details</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input v-model="newBank.bank_name" placeholder="Bank name" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
            <input v-model="newBank.account_name" placeholder="Account name" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
            <input v-model="newBank.account_number" placeholder="Account number" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
            <input v-model="newBank.currency" placeholder="Currency" value="NGN" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
          </div>
        </div>

        <!-- Transactions Table -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
              <tr>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">#</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Date</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Amount</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Reference</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Description</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Counterparty</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600 w-10">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, i) in mappedRows"
                :key="i"
                class="border-b border-slate-100 hover:bg-slate-50 transition-colors"
                :class="!rowValidation[i]?.valid ? 'bg-red-50/30' : ''"
              >
                <td class="py-2 px-4 text-slate-400 text-xs">{{ i + 1 }}</td>
                <td class="py-2 px-4">
                  <input
                    v-model="row.transaction_date"
                    class="w-28 px-2 py-1.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                    :class="!row.transaction_date ? 'border-red-300 bg-red-50' : ''"
                  />
                </td>
                <td class="py-2 px-4">
                  <input
                    v-model="row.amount"
                    class="w-28 px-2 py-1.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                    :class="!row.amount ? 'border-red-300 bg-red-50' : ''"
                  />
                </td>
                <td class="py-2 px-4">
                  <input v-model="row.reference" class="w-32 px-2 py-1.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                </td>
                <td class="py-2 px-4">
                  <input v-model="row.description" class="min-w-[150px] px-2 py-1.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                </td>
                <td class="py-2 px-4">
                  <input v-model="row.counterparty" class="min-w-[120px] px-2 py-1.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
                </td>
                <td class="py-2 px-4 text-center">
                  <span v-if="rowValidation[i]?.valid" class="inline-flex items-center gap-1 text-green-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </span>
                  <span v-else class="inline-flex items-center gap-1 text-red-500" title="Invalid data">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Validation Summary -->
        <div v-if="invalidRowCount > 0" class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <p class="font-medium text-amber-800">{{ invalidRowCount }} row(s) have validation errors</p>
              <p class="text-sm text-amber-700 mt-1">Please fix the highlighted fields or remove invalid rows before importing.</p>
            </div>
          </div>
        </div>

        <!-- Import Results -->
        <div v-if="importResults" class="mt-4 rounded-xl p-4" :class="(importResults.results?.failed > 0) || (importResults.errors && importResults.errors.length) ? 'bg-amber-50 border border-amber-200' : 'bg-green-50 border border-green-200'">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" :class="importResults.results?.failed > 0 ? 'text-amber-600' : 'text-green-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="importResults.results?.failed > 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="font-medium" :class="(importResults.results?.failed > 0) || (importResults.errors && importResults.errors.length) ? 'text-amber-800' : 'text-green-800'">
                {{ importResults.message || 'No message returned from server' }}
              </p>
              <div v-if="importResults.results" class="text-sm mt-1" :class="importResults.results?.failed > 0 ? 'text-amber-700' : 'text-green-700'">
                <span>✅ {{ importResults.results.success ?? importResults.results.created ?? 0 }} imported</span>
                <span v-if="importResults.results?.failed > 0" class="ml-3">❌ {{ importResults.results.failed }} failed</span>
              </div>

              <ul v-if="importResults.errors && importResults.errors.length" class="mt-2 list-disc list-inside text-sm text-amber-700">
                <li v-for="(err, i) in importResults.errors" :key="i">{{ err }}</li>
              </ul>

              <details v-if="importResults.raw" class="mt-2 text-xs text-slate-500">
                <summary class="cursor-pointer">Show raw response</summary>
                <pre class="whitespace-pre-wrap text-xs mt-2">{{ JSON.stringify(importResults.raw, null, 2) }}</pre>
              </details>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex flex-col sm:flex-row justify-between gap-3 pt-4 border-t border-slate-100">
          <button @click="step = 2" class="text-slate-600 hover:text-slate-800 px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
            ← Back to Mapping
          </button>
          <button
            @click="submitImport"
            :disabled="isImporting || validRowCount === 0"
            class="group inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed text-white px-6 py-2.5 rounded-xl font-medium transition-all"
          >
            <svg v-if="isImporting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span v-else>{{ validRowCount > 0 ? `Import ${validRowCount} Transaction${validRowCount !== 1 ? 's' : ''}` : 'No valid transactions' }}</span>
            <svg v-if="!isImporting && validRowCount > 0" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </BusinessLayout>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'
import axios from 'axios'
import { Inertia } from '@inertiajs/inertia'
import Papa from 'papaparse'
import * as XLSX from 'xlsx'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

// State
const file = ref(null)
const fileName = ref('')
const headers = ref([])
const parsedRows = ref([])
const sampleRows = ref([])
const mapping = ref({})
const step = ref(1)
const parseError = ref('')
const isMappingLoading = ref(false)
const mappedRows = ref([])
const isImporting = ref(false)
const importResults = ref(null)
const isDragging = ref(false)
const fileInput = ref(null)

// Steps configuration
const steps = [
  { label: 'Upload File', icon: 'upload' },
  { label: 'Map Columns', icon: 'mapping' },
  { label: 'Review & Import', icon: 'check' }
]

// Page props
const page = usePage()
const bankAccounts = computed(() => page.props.bankAccounts || [])
const selectedBankAccountId = ref(bankAccounts.value.length ? bankAccounts.value[0].id : 'new')
const newBank = ref({ bank_name: '', account_name: '', account_number: '', currency: 'NGN' })

// Helper functions
function getFieldLabel(field) {
  const labels = {
    transaction_date: 'Date',
    amount: 'Amount',
    description: 'Description',
    reference: 'Reference',
    counterparty: 'Counterparty',
    balance: 'Balance',
    currency: 'Currency',
    type: 'Type',
    category: 'Category'
  }
  return labels[field] || field
}

function normalizeDate(val) {
  if (val === null || val === undefined || val === '') return ''
  const n = Number(String(val).replace(/\s+/g, ''))
  if (!isNaN(n) && n > 0 && n < 2958465) {
    const excelEpoch = new Date(1899, 11, 30)
    const date = new Date(excelEpoch.getTime() + n * 86400000)
    if (!isNaN(date.getTime())) return date.toISOString().split('T')[0]
  }
  const d = new Date(val)
  if (!isNaN(d.getTime())) return d.toISOString().split('T')[0]
  const parts = String(val).split(/[\/\-\.]/)
  if (parts.length === 3) {
    const [a, b, c] = parts
    if (c.length === 4) {
      const tryDate = new Date(`${c}-${b.padStart(2, '0')}-${a.padStart(2, '0')}`)
      if (!isNaN(tryDate.getTime())) return tryDate.toISOString().split('T')[0]
    }
  }
  return String(val)
}

function normalizeAmount(val) {
  if (val === null || val === undefined || val === '') return ''
  let s = String(val).trim()
  const isNegative = /^\(.*\)$/.test(s)
  s = s.replace(/^[^0-9\-\(]+/, '')
  s = s.replace(/[^0-9.\-,()]/g, '')
  s = s.replace(/[,\s]/g, '')
  s = s.replace(/[()]/g, '')
  if (s === '' || s === '-') return ''
  const num = Number(s)
  if (isNaN(num)) return String(val)
  return isNegative ? -Math.abs(num) : num
}

// File handling
function triggerFileInput() {
  fileInput.value?.click()
}

function handleDrop(e) {
  isDragging.value = false
  const files = e.dataTransfer.files
  if (files.length > 0) processFile(files[0])
}

function onFileChange(e) {
  const f = e.target.files[0]
  if (f) processFile(f)
}

function clear() {
  file.value = null
  fileName.value = ''
  headers.value = []
  parsedRows.value = []
  sampleRows.value = []
  mapping.value = {}
  mappedRows.value = []
  parseError.value = ''
  step.value = 1
  importResults.value = null
}

function processFile(f) {
  file.value = f
  fileName.value = f.name
  parseError.value = ''
  const ext = f.name.split('.').pop().toLowerCase()
  if (ext === 'csv') return parseCSV(f)
  return parseExcel(f)
}

function parseCSV(f) {
  Papa.parse(f, {
    header: true,
    skipEmptyLines: true,
    complete: (results) => {
      if (results.errors.length > 0 && results.data.length === 0) {
        parseError.value = 'Failed to parse CSV: ' + results.errors[0].message
        return
      }
      parsedRows.value = results.data || []
      headers.value = results.meta.fields || (parsedRows.value[0] ? Object.keys(parsedRows.value[0]) : [])
      sampleRows.value = parsedRows.value.slice(0, 10)
      mapping.value = {}
      headers.value.forEach(h => mapping.value[h] = 'ignore')
    },
    error: (err) => {
      parseError.value = 'Failed to read CSV file: ' + err.message
    }
  })
}

function parseExcel(f) {
  const reader = new FileReader()
  reader.onload = (e) => {
    try {
      const data = new Uint8Array(e.target.result)
      const workbook = XLSX.read(data, { type: 'array' })
      const firstSheet = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[firstSheet]
      const jsonData = XLSX.utils.sheet_to_json(worksheet, { defval: '' })
      if (jsonData.length === 0) {
        parseError.value = 'The file appears to be empty. Please check your data.'
        return
      }
      parsedRows.value = jsonData
      headers.value = Object.keys(jsonData[0])
      sampleRows.value = jsonData.slice(0, 10)
      mapping.value = {}
      headers.value.forEach(h => mapping.value[h] = 'ignore')
    } catch (err) {
      parseError.value = 'Failed to read Excel file: ' + err.message
    }
  }
  reader.readAsArrayBuffer(f)
}

// Mapping
async function autoMap() {
  if (!headers.value.length) return
  isMappingLoading.value = true
  try {
    const res = await axios.post('/business/transactions/import/map-columns', {
      headers: headers.value,
      sample_rows: sampleRows.value
    }, { timeout: 180000 })
    const aiMap = res.data.mapping || {}
    Object.keys(aiMap).forEach(h => { mapping.value[h] = aiMap[h] || 'ignore' })
  } catch (err) {
    console.error('AI mapping error', err)
  } finally {
    isMappingLoading.value = false
  }
}

function fuzzySuggestAll() {
  const aliases = {
    transaction_date: ['date', 'transaction date', 'posted at', 'posted', 'value date', 'trans date', 'booking date'],
    amount: ['amount', 'amt', 'value', 'credit', 'debit', 'transaction amount', 'sum', 'money'],
    description: ['description', 'narration', 'details', 'remark', 'remarks', 'memo'],
    reference: ['reference', 'ref', 'transaction id', 'trx id', 'id', 'payment reference'],
    counterparty: ['counterparty', 'merchant', 'payee', 'payer', 'beneficiary', 'description name'],
    balance: ['balance', 'running balance', 'account balance'],
    currency: ['currency', 'cur', 'ccy'],
    type: ['type', 'transaction type', 'credit/debit', 'debit', 'credit'],
    category: ['category', 'cat', 'classification']
  }

  const suggestions = {}
  headers.value.forEach(h => {
    const normalized = String(h).toLowerCase().replace(/[^a-z0-9 ]/g, ' ')
    let found = null
    for (const [field, keys] of Object.entries(aliases)) {
      for (const k of keys) {
        if (normalized === k || normalized.includes(k) || k.includes(normalized)) {
          found = field
          break
        }
      }
      if (found) break
    }
    suggestions[h] = found
  })
  return suggestions
}

const suggestions = computed(() => fuzzySuggestAll())

function refreshSuggestions() {
  // Computed will update automatically
}

function applySuggestion(header) {
  const s = suggestions.value[header]
  if (s) mapping.value[header] = s
}

function applyAllSuggestions() {
  const s = suggestions.value
  Object.keys(s).forEach(h => { if (s[h]) mapping.value[h] = s[h] })
}

const requiredFields = ['transaction_date', 'amount']
const mappingErrors = computed(() => {
  const errors = []
  const mapped = Object.values(mapping.value || {})
  for (const field of requiredFields) {
    if (!mapped.includes(field)) {
      const label = field === 'transaction_date' ? 'Transaction Date' : 'Amount'
      errors.push(`${label} is required but not mapped to any column`)
    }
  }
  return errors
})

function goToMapping() {
  if (!parsedRows.value.length) return
  step.value = 2
  isMappingLoading.value = true
  autoMap().finally(() => { isMappingLoading.value = false })
}

function applyMapping() {
  const mapped = []
  for (const row of parsedRows.value) {
    const out = {}
    for (const h of headers.value) {
      const field = mapping.value[h]
      if (!field || field === 'ignore') continue
      let raw = row[h]
      if (field === 'transaction_date') raw = normalizeDate(raw)
      if (field === 'amount' || field === 'balance') raw = normalizeAmount(raw)
      out[field] = raw
    }
    if (Object.keys(out).length) mapped.push(out)
  }
  mappedRows.value = mapped
  step.value = 3
}

const rowValidation = computed(() => {
  return mappedRows.value.map((row) => {
    const errors = []
    if (!row.transaction_date) errors.push('Date is required')
    if (row.amount === '' || row.amount === null || isNaN(Number(row.amount))) errors.push('Valid amount is required')
    return { valid: errors.length === 0, errors }
  })
})

const validRowCount = computed(() => rowValidation.value.filter(v => v.valid).length)
const invalidRowCount = computed(() => rowValidation.value.filter(v => !v.valid).length)

async function submitImport() {
  if (validRowCount.value === 0) return
  isImporting.value = true
  importResults.value = null

  let rowsSource = Array.isArray(mappedRows.value) ? mappedRows.value : []
  let validRows = rowsSource.filter((_, i) => rowValidation.value[i]?.valid)

  try {
    if (selectedBankAccountId.value === 'new') {
      if (!newBank.value.bank_name || !newBank.value.account_number) {
        importResults.value = { message: 'Please provide bank name and account number to create the bank account.' }
        isImporting.value = false
        return
      }
    }

    const payloadRows = validRows.map(r => ({
      ...r,
      bank_account_id: selectedBankAccountId.value !== 'new' ? selectedBankAccountId.value : null
    }))

    const body = { mapped_rows: payloadRows }
    if (selectedBankAccountId.value !== 'new') {
      body.bank_account_id = selectedBankAccountId.value
    } else {
      body.bank_name = newBank.value.bank_name
      body.account_name = newBank.value.account_name
      body.account_number = newBank.value.account_number
      body.currency = newBank.value.currency
    }

    // Use Inertia.post so server can return a redirect with a session flash message
    Inertia.post('/business/transactions/import/process', body, {
      onSuccess: (page) => {
        // If server redirected with a flash, Inertia will handle navigation.
        // Otherwise, try to show returned JSON-ish props if any.
        if (page.props && page.props.importResults) {
          importResults.value = page.props.importResults
        } else {
          importResults.value = { message: 'Import complete.' }
        }
      },
      onError: (errs) => {
        // errs is an object of validation errors
        const arr = []
        Object.values(errs || {}).forEach(v => { if (Array.isArray(v)) arr.push(...v); else if (v) arr.push(v) })
        importResults.value = { message: 'Import failed.', errors: arr }
      },
      onFinish: () => {
        isImporting.value = false
      }
    })
    return

    // Clear after successful import? Let user decide
  } catch (err) {
    console.error('Import failed', err)
    const serverData = err.response?.data
    let userMessage = 'Import failed.'
    if (serverData && typeof serverData === 'object') {
      userMessage = serverData.message || serverData.msg || JSON.stringify(serverData)
    } else if (serverData && typeof serverData === 'string') {
      userMessage = serverData
    } else if (err.message) {
      userMessage = err.message
    }
    if (err.response?.status === 419 || err.response?.status === 401) {
      userMessage = 'Session expired. Please refresh and try again.'
    }

    const errors = serverData?.errors || serverData?.validation_errors || serverData?.rows_errors || []
    importResults.value = {
      message: userMessage,
      errors: Array.isArray(errors) ? errors : (errors ? [errors] : []),
      results: serverData?.results || serverData?.data || null,
      raw: serverData || { message: userMessage },
      status: err.response?.status || null
    }
  } finally {
    isImporting.value = false
  }
}
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Custom scrollbar for tables */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
  width: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Focus styles */
input:focus, select:focus {
  outline: none;
}

/* Input number spinner removal */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
