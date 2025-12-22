<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-50">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white overflow-y-auto overflow-x-hidden">
      <HeaderBar title="Simulasi Investasi" class="mt-3" />
      <div class="my-4 border-b border-gray-200"></div>

      <div class="pb-12 max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 transition-all hover:shadow-md">
          <div class="flex items-center gap-2 mb-6 border-b border-gray-100 pb-3">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#074a5d]" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm3-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd" />
            </svg>
            <h3 class="text-[#074a5d] font-bold text-base">Parameter Investasi</h3>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            
            <div class="group">
              <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-[#074a5d] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Modal Awal (Rp)
              </label>
              <input type="number" v-model.number="params.initial" @input="debouncedCalculate"
                class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-[#074a5d] focus:ring-2 focus:ring-[#074a5d]/20 outline-none transition-all shadow-sm"
                placeholder="0" />
            </div>

            <div class="group">
              <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-[#074a5d] transition-colors">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Setoran Bulanan (Rp)
              </label>
              <input type="number" v-model.number="params.monthly" @input="debouncedCalculate"
                class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-[#074a5d] focus:ring-2 focus:ring-[#074a5d]/20 outline-none transition-all shadow-sm"
                placeholder="0" />
            </div>

            <div class="group">
               <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-[#074a5d] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                Return (% / Tahun)
              </label>
              <input type="number" v-model.number="params.rate" @input="debouncedCalculate"
                class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-[#074a5d] focus:ring-2 focus:ring-[#074a5d]/20 outline-none transition-all shadow-sm"
                placeholder="Contoh: 5" />
            </div>

            <div class="group">
               <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-red-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                </svg>
                Pajak / Tax (%)
              </label>
              <input type="number" v-model.number="params.tax" @input="debouncedCalculate"
                class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-200 outline-none transition-all shadow-sm"
                placeholder="Contoh: 20" />
              <p class="text-[10px] text-gray-400 mt-1 ml-1">*Pph Final (Deposito: 20%, SBN: 10%)</p>
            </div>

            <div class="group">
               <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-[#074a5d] transition-colors">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Durasi (Tahun)
              </label>
              <input type="number" v-model.number="params.years" @input="debouncedCalculate"
                class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 placeholder-gray-400 focus:border-[#074a5d] focus:ring-2 focus:ring-[#074a5d]/20 outline-none transition-all shadow-sm"
                placeholder="Contoh: 10" />
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          
          <div class="summary-card bg-white p-5 rounded-xl shadow-sm border border-gray-200 relative overflow-hidden group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="absolute right-[-10px] bottom-[-10px] opacity-5 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-[#074a5d]" fill="currentColor" viewBox="0 0 20 20">
                 <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" /><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="relative z-10">
              <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Disetor</div>
              <div class="text-2xl font-extrabold text-[#074a5d] mt-2 tracking-tight number-transition">
                {{ formatRupiah(summary.totalInvested) }}
              </div>
              <div class="text-sm text-gray-400 mt-1 font-medium">Modal Pokok Anda</div>
            </div>
          </div>

          <div class="summary-card bg-white p-5 rounded-xl shadow-sm border border-gray-200 relative overflow-hidden group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
             <div class="absolute right-[-10px] bottom-[-10px] opacity-5 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="relative z-10">
              <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Bunga Bersih (Net)</div>
              <div class="text-2xl font-extrabold text-green-600 mt-2 tracking-tight number-transition">
                + {{ formatRupiah(summary.totalInterest) }}
              </div>
              <div class="text-sm text-gray-400 mt-1 font-medium">Sudah dipotong pajak {{ params.tax }}%</div>
            </div>
          </div>

          <div class="summary-card bg-gradient-to-br from-[#074a5d] to-[#053341] p-5 rounded-xl shadow-md text-white relative overflow-hidden group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
             <div class="absolute right-[-10px] bottom-[-10px] opacity-10 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1H4a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V5a1 1 0 10-1 1h1zm3 0a1 1 0 10-1-1v1h1z" clip-rule="evenodd" />
                <path d="M9 11H3v5a2 2 0 002 2h4v-7zM11 18h4a2 2 0 002-2v-5h-6v7z" />
              </svg>
            </div>
            <div class="relative z-10">
              <div class="text-xs text-gray-200 font-bold uppercase tracking-wider opacity-90">Estimasi Saldo Akhir</div>
              <div class="text-3xl font-extrabold mt-2 tracking-tight number-transition">
                {{ formatRupiah(summary.finalBalance) }}
              </div>
              <div class="text-sm text-gray-300 mt-1 font-medium opacity-90">Setelah {{ params.years }} Tahun</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
          <div class="flex flex-wrap gap-2 justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#074a5d]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Rincian Proyeksi Bulanan (Net)
            </h3>
            <button @click="downloadExcel" class="group flex items-center gap-2 px-4 py-2 bg-white border border-green-600 text-green-700 rounded-lg text-sm font-bold hover:bg-green-50 transition-all duration-200 active:scale-95">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:-translate-y-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Download Excel
            </button>
          </div>

          <div class="overflow-x-auto max-h-[550px] relative">
            <table class="min-w-full table-auto border-collapse">
              <thead class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider font-bold sticky top-0 z-20 shadow-sm">
                <tr>
                  <th class="p-4 border-b border-r border-gray-200 text-center w-20 bg-gray-100">Bulan</th>
                  <th class="p-4 border-b border-r border-gray-200 text-right bg-gray-100">Saldo Awal</th>
                  <th class="p-4 border-b border-r border-gray-200 text-right bg-gray-100">Setoran (+)</th>
                  <th class="p-4 border-b border-r border-gray-200 text-right bg-gray-100">Bunga Net<span v-if="params.tax > 0" class="text-[10px] text-red-500 block normal-case">(Tax {{params.tax}}%)</span></th>
                  <th class="p-4 border-b border-gray-200 text-right text-[#074a5d] bg-gray-100">Saldo Akhir (=)</th>
                </tr>
              </thead>
              <transition-group name="list" tag="tbody" class="text-sm text-gray-700 bg-white relative z-10">
                <template v-if="simulationData.length > 0">
                  <tr v-for="row in simulationData" :key="row.month" class="hover:bg-blue-50/50 border-b border-gray-100 transition-colors duration-150">
                    <td class="p-3 border-r border-gray-100 text-center font-bold text-gray-500">{{ row.month }}</td>
                    <td class="p-3 border-r border-gray-100 text-right font-medium">{{ formatRupiah(row.startBalance) }}</td>
                    <td class="p-3 border-r border-gray-100 text-right">{{ formatRupiah(row.contribution) }}</td>
                    <td class="p-3 border-r border-gray-100 text-right font-bold text-green-600">
                      + {{ formatRupiah(row.netInterest) }}
                    </td>
                    <td class="p-3 text-right font-extrabold text-[#074a5d]">{{ formatRupiah(row.endBalance) }}</td>
                  </tr>
                </template>
                <tr v-else key="empty">
                  <td colspan="5" class="p-12 text-center text-gray-400 flex flex-col items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Silakan masukkan parameter investasi untuk melihat hasil simulasi.
                  </td>
                </tr>
              </transition-group>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import debounce from 'lodash/debounce';

export default {
  name: "InvestmentSimulation",
  components: { Sidebar, HeaderBar },
  data() {
    return {
      activeMenu: "finance-simulation",
      params: {
        initial: 10000000,
        monthly: 1000000,
        rate: 6,      // Return per tahun
        tax: 0,       // Pajak dalam persen
        years: 5
      },
      summary: {
        totalInvested: 0,
        totalInterest: 0,
        finalBalance: 0
      },
      simulationData: [],
      debouncedCalculate: null
    };
  },
  created() {
    this.debouncedCalculate = debounce(this.calculateInvestment, 300);
  },
  mounted() {
    this.calculateInvestment();
  },
  methods: {
    formatRupiah(angka) {
      if (angka === null || angka === undefined || isNaN(angka)) return "Rp 0";
      return "Rp " + Math.round(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },

    calculateInvestment() {
      // Validasi input
      if (this.params.years < 0) this.params.years = 0;
      if (this.params.rate < 0) this.params.rate = 0;
      if (this.params.initial < 0) this.params.initial = 0;
      if (this.params.monthly < 0) this.params.monthly = 0;
      if (this.params.tax < 0) this.params.tax = 0;

      const months = Math.floor(this.params.years * 12);
      // Rate bulanan gross
      const monthlyRate = (this.params.rate / 100) / 12;
      const taxRate = this.params.tax / 100;
      
      let currentBalance = Number(this.params.initial) || 0;
      let totalContributed = Number(this.params.initial) || 0;
      const monthlyContribution = Number(this.params.monthly) || 0;
      let data = [];

      if (months > 0) {
        for (let i = 1; i <= months; i++) {
          const startBalance = currentBalance;
          
          // 1. Hitung Bunga Kotor (Gross)
          const grossInterest = startBalance * monthlyRate;
          
          // 2. Hitung Nominal Pajak
          const taxAmount = grossInterest * taxRate;
          
          // 3. Hitung Bunga Bersih (Net)
          const netInterest = grossInterest - taxAmount;

          // 4. Saldo Akhir = Awal + Bunga Net + Setoran
          const endBalance = startBalance + netInterest + monthlyContribution;

          data.push({
            month: i,
            startBalance: startBalance,
            contribution: monthlyContribution,
            grossInterest: grossInterest,
            taxAmount: taxAmount,
            netInterest: netInterest,
            endBalance: endBalance
          });

          currentBalance = endBalance;
          totalContributed += monthlyContribution;
        }
      } else if (currentBalance > 0 && months === 0) {
         this.summary.totalInvested = currentBalance;
         this.summary.finalBalance = currentBalance;
         this.summary.totalInterest = 0;
         this.simulationData = [];
         return;
      }

      this.simulationData = data;
      this.summary.totalInvested = totalContributed;
      this.summary.finalBalance = currentBalance;
      this.summary.totalInterest = currentBalance - totalContributed;
    },

    async downloadExcel() {
      const workbook = new ExcelJS.Workbook();
      const ws = workbook.addWorksheet("Simulasi Investasi");

      // STYLE DEFINITIONS
      const headerStyle = { font: { bold: true, color: { argb: "FFFFFFFF" }, size: 11 }, fill: { type: "pattern", pattern: "solid", fgColor: { argb: "FF074A5D" } }, alignment: { horizontal: "center", vertical: "middle" }, border: { top: { style: "thin" }, bottom: { style: "thin" }, left: {style:'thin'}, right: {style:'thin'} } };
      const currencyFmt = '"Rp "#,##0';

      ws.mergeCells("A1:G1");
      const title = ws.getCell("A1");
      title.value = "SIMULASI INVESTASI (DENGAN PAJAK)";
      title.font = { name: "Arial", size: 14, bold: true, color: { argb: "FF074A5D" } };
      title.alignment = { horizontal: "center", vertical: "middle" };

      ws.addRow([]); 

      // INFO PARAMETER
      ws.addRow(["Parameter", "Nilai"]).font = { bold: true };
      ws.addRow(["Modal Awal", this.params.initial]).getCell(2).numFmt = currencyFmt;
      ws.addRow(["Setoran Bulanan", this.params.monthly]).getCell(2).numFmt = currencyFmt;
      ws.addRow(["Return Per Tahun", (this.params.rate || 0) + "%"]);
      ws.addRow(["Pajak (Tax)", (this.params.tax || 0) + "%"]); // Info Pajak
      ws.addRow(["Durasi", (this.params.years || 0) + " Tahun"]);
      ws.addRow([]); 

      ws.addRow(["RANGKUMAN HASIL", ""]).font = { bold: true, color: { argb: "FF074A5D" } };
      ws.addRow(["Total Disetor", this.summary.totalInvested]).getCell(2).numFmt = currencyFmt;
      
      const interestRow = ws.addRow(["Total Bunga (Net)", this.summary.totalInterest]);
      interestRow.getCell(2).numFmt = currencyFmt;
      interestRow.getCell(2).font = { color: { argb: "FF008000" } };
      
      const finalRow = ws.addRow(["Saldo Akhir", this.summary.finalBalance]);
      finalRow.getCell(2).numFmt = currencyFmt;
      finalRow.getCell(2).font = { bold: true };
      ws.addRow([]);

      // HEADER TABEL (Menambahkan kolom Gross & Tax di Excel agar detail)
      const headerRow = ws.addRow([
        "BULAN KE", "SALDO AWAL", "SETORAN", "BUNGA (GROSS)", "PAJAK", "BUNGA (NET)", "SALDO AKHIR"
      ]);
      headerRow.eachCell((cell) => { Object.assign(cell, headerStyle); });

      // ISI DATA
      this.simulationData.forEach(row => {
        const excelRow = ws.addRow([
          row.month,
          row.startBalance,
          row.contribution,
          row.grossInterest,
          row.taxAmount,
          row.netInterest,
          row.endBalance
        ]);

        // Format Currency & Alignment
        [2, 3, 4, 5, 6, 7].forEach(colIdx => {
          const cell = excelRow.getCell(colIdx);
          cell.numFmt = currencyFmt;
          cell.alignment = { horizontal: "right" };
          cell.border = { bottom: {style:'dotted', color: {argb: 'FFCCCCCC'}} };
        });
        excelRow.getCell(1).alignment = { horizontal: "center" };
        
        // Warna Pajak Merah
        excelRow.getCell(5).font = { color: { argb: "FFCC0000" } }; // Tax Red
        // Warna Net Hijau
        excelRow.getCell(6).font = { color: { argb: "FF008000" } }; // Net Green
        // Akhir Bold
        excelRow.getCell(7).font = { bold: true, color: { argb: "FF074A5D" } };
      });

      // AUTO WIDTH
      ws.getColumn(1).width = 12;
      ws.getColumn(2).width = 20;
      ws.getColumn(3).width = 18;
      ws.getColumn(4).width = 18;
      ws.getColumn(5).width = 15;
      ws.getColumn(6).width = 18;
      ws.getColumn(7).width = 22;

      const buffer = await workbook.xlsx.writeBuffer();
      const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
      saveAs(blob, `Simulasi_Investasi_Net_Tax.xlsx`);
    }
  }
};
</script>

<style scoped>
/* CSS sama seperti sebelumnya */
.list-move,
.list-enter-active,
.list-leave-active {
  transition: all 0.4s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

.list-leave-active {
  position: absolute;
  width: 100%;
  z-index: 0;
}

.number-transition {
   transition: color 0.3s ease, transform 0.3s ease;
}

.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1; 
    border-radius: 10px;
}
.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8; 
}

.input-smooth:focus {
    box-shadow: 0 0 0 3px rgba(7, 74, 93, 0.15);
}
</style>