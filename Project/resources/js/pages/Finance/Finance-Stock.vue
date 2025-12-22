<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-50">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white overflow-y-auto overflow-x-hidden">
      <HeaderBar title="Simulasi Saham (DCA)" class="mt-3" />
      <div class="my-4 border-b border-gray-200"></div>

      <div class="pb-12 max-w-7xl mx-auto">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 transition-all hover:shadow-md">
          <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-3">
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
              </svg>
              <h3 class="text-indigo-600 font-bold text-base">Parameter Pasar</h3>
            </div>
            <button @click="calculateStock" class="text-xs flex items-center gap-1 font-bold text-gray-500 hover:text-indigo-600 transition-colors bg-gray-100 hover:bg-indigo-50 px-3 py-1.5 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Simulasi Ulang Pasar
            </button>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="group">
              <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-indigo-600 transition-colors">
                Modal Awal (Rp)
              </label>
              <input type="number" v-model.number="params.initial" @input="debouncedCalculate"
                class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 outline-none transition-all shadow-sm" />
            </div>

            <div class="group">
              <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-indigo-600 transition-colors">
                Beli Rutin / Bulan (Rp)
              </label>
              <input type="number" v-model.number="params.monthly" @input="debouncedCalculate"
                class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 outline-none transition-all shadow-sm" />
            </div>

            <div class="group">
              <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-indigo-600 transition-colors">
                Durasi (Tahun)
              </label>
              <input type="number" v-model.number="params.years" @input="debouncedCalculate"
                class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 outline-none transition-all shadow-sm" />
            </div>

            <div class="group">
              <label class="flex items-center gap-1 text-xs font-bold text-gray-500 uppercase mb-2 group-hover:text-indigo-600 transition-colors">
                Volatilitas Pasar (Resiko)
              </label>
              <select v-model="params.volatility" @change="calculateStock" class="input-smooth w-full border border-gray-300 rounded-lg py-2.5 px-4 text-sm text-gray-800 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 outline-none transition-all shadow-sm bg-white cursor-pointer">
                <option :value="0.02">Rendah (Stabil)</option>
                <option :value="0.05">Sedang (Normal)</option>
                <option :value="0.10">Tinggi (Agresif)</option>
                <option :value="0.15">Sangat Tinggi (Crypto)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          
          <div class="summary-card bg-white p-5 rounded-xl shadow-sm border border-gray-200 relative overflow-hidden group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
            <div class="absolute right-[-10px] bottom-[-10px] opacity-5 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                 <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" /><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="relative z-10">
              <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Modal Disetor</div>
              <div class="text-2xl font-extrabold text-gray-700 mt-2 tracking-tight number-transition">
                {{ formatRupiah(summary.totalInvested) }}
              </div>
              <div class="text-sm text-gray-400 mt-1 font-medium">
                Total Lot: <span class="text-indigo-600 font-bold">{{ formatNumber(summary.totalLots) }} Lot</span>
              </div>
            </div>
          </div>

          <div class="summary-card bg-white p-5 rounded-xl shadow-sm border border-gray-200 relative overflow-hidden group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
             <div class="absolute right-[-10px] bottom-[-10px] opacity-5 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
               <svg v-if="summary.isProfit" xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="relative z-10">
              <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Keuntungan / Kerugian</div>
              <div class="text-2xl font-extrabold mt-2 tracking-tight number-transition"
                 :class="summary.isProfit ? 'text-green-600' : 'text-red-600'">
                {{ summary.isProfit ? '+' : '' }} {{ formatRupiah(summary.totalGainLoss) }}
              </div>
              <div class="text-sm mt-1 font-bold flex items-center gap-1" :class="summary.isProfit ? 'text-green-500' : 'text-red-500'">
                 <span v-if="summary.isProfit">▲</span><span v-else>▼</span>
                 {{ summary.percentageGain }}%
              </div>
            </div>
          </div>

          <div class="summary-card bg-gradient-to-br from-indigo-800 to-indigo-950 p-5 rounded-xl shadow-md text-white relative overflow-hidden group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
             <div class="absolute right-[-10px] bottom-[-10px] opacity-10 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
              </svg>
            </div>
            <div class="relative z-10">
              <div class="text-xs text-indigo-200 font-bold uppercase tracking-wider opacity-90">Nilai Portfolio Sekarang</div>
              <div class="text-3xl font-extrabold mt-2 tracking-tight number-transition">
                {{ formatRupiah(summary.currentValue) }}
              </div>
              <div class="text-sm text-indigo-300 mt-1 font-medium opacity-90">
                 Harga Saham Akhir: {{ formatRupiah(stockInfo.currentPrice) }}
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
          <div class="flex flex-wrap gap-2 justify-between items-center px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Riwayat Pergerakan Pasar
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
                  <th class="p-4 border-b border-r border-gray-200 text-right bg-gray-100">Harga Saham</th>
                  <th class="p-4 border-b border-r border-gray-200 text-center bg-gray-100">Perubahan</th>
                  <th class="p-4 border-b border-r border-gray-200 text-right bg-gray-100">Investasi (DCA)</th>
                  <th class="p-4 border-b border-r border-gray-200 text-right bg-gray-100">Dapat Lot</th>
                  <th class="p-4 border-b border-gray-200 text-right text-indigo-700 bg-gray-100">Nilai Aset</th>
                </tr>
              </thead>
              <transition-group name="list" tag="tbody" class="text-sm text-gray-700 bg-white relative z-10">
                <template v-if="simulationData.length > 0">
                  <tr v-for="row in simulationData" :key="row.month" class="hover:bg-indigo-50/50 border-b border-gray-100 transition-colors duration-150">
                    <td class="p-3 border-r border-gray-100 text-center font-bold text-gray-500">{{ row.month }}</td>
                    <td class="p-3 border-r border-gray-100 text-right font-medium text-gray-800">{{ formatRupiah(row.stockPrice) }}</td>
                    <td class="p-3 border-r border-gray-100 text-center font-bold text-xs">
                        <span :class="row.changePct >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded-full">
                           {{ row.changePct >= 0 ? '▲' : '▼' }} {{ Math.abs(row.changePct).toFixed(2) }}%
                        </span>
                    </td>
                    <td class="p-3 border-r border-gray-100 text-right text-gray-500">{{ formatRupiah(row.investment) }}</td>
                    <td class="p-3 border-r border-gray-100 text-right font-mono text-xs">{{ row.lotsAcquired.toFixed(2) }} Lot</td>
                    <td class="p-3 text-right font-extrabold text-indigo-700">{{ formatRupiah(row.portfolioValue) }}</td>
                  </tr>
                </template>
                <tr v-else key="empty">
                  <td colspan="6" class="p-12 text-center text-gray-400">
                    Belum ada data simulasi.
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
  name: "StockSimulation",
  components: { Sidebar, HeaderBar },
  data() {
    return {
      activeMenu: "finance-stock",
      params: {
        initial: 10000000,
        monthly: 1000000,
        years: 5,
        volatility: 0.05, // 5% standard deviation per month (Medium Risk)
      },
      stockInfo: {
        startPrice: 1000, // Harga awal akan dirandom saat created
        currentPrice: 0
      },
      summary: {
        totalInvested: 0,
        totalLots: 0,
        currentValue: 0,
        totalGainLoss: 0,
        percentageGain: 0,
        isProfit: true
      },
      simulationData: [],
      debouncedCalculate: null
    };
  },
  created() {
    this.debouncedCalculate = debounce(this.calculateStock, 300);
    // Randomize harga awal saham antara Rp 800 - Rp 2500 saat pertama buka
    this.stockInfo.startPrice = Math.floor(Math.random() * (2500 - 800 + 1) + 800);
  },
  mounted() {
    this.calculateStock();
  },
  methods: {
    formatRupiah(angka) {
        if (angka === null || angka === undefined || isNaN(angka)) return "Rp 0";
        // Handle negatif dengan kurung atau minus
        let prefix = angka < 0 ? "- Rp " : "Rp ";
        return prefix + Math.round(Math.abs(angka)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    formatNumber(angka) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(angka);
    },

    // Fungsi utilitas untuk Generate Random Normal Distribution (Box-Muller Transform)
    // Agar pergerakan saham terasa lebih alami (Bell Curve), bukan random rata.
    randomNormal(mean, stdDev) {
        let u = 0, v = 0;
        while(u === 0) u = Math.random(); 
        while(v === 0) v = Math.random();
        let z = Math.sqrt( -2.0 * Math.log( u ) ) * Math.cos( 2.0 * Math.PI * v );
        return mean + z * stdDev;
    },

    calculateStock() {
      // 1. Reset Data
      this.simulationData = [];
      let currentPrice = this.stockInfo.startPrice; // Gunakan harga start yang sudah di-set
      let totalLots = 0; // Dalam satuan lot (1 lot = 100 lembar)
      let totalMoneyOut = 0;
      
      const months = Math.floor(this.params.years * 12);
      
      // Jika user menekan tombol "Simulasi Ulang", kita bisa merandom harga start lagi sedikit
      // Tapi lebih baik harga start tetap, tapi "nasib" perjalanannya yang berubah (random walk)
      
      // Expected Monthly Return (Rata-rata kenaikan pasar saham jangka panjang ~10-12% per tahun)
      // Kita set base drift kecil positif, lalu ditambah volatility
      const baseDrift = 0.008; // ~0.8% per month (~10% per year)

      // Initial Investment (Beli Lot di Bulan 0)
      if (this.params.initial > 0) {
         totalMoneyOut += this.params.initial;
         // Anggap beli di harga start
         const lotsBought = (this.params.initial / currentPrice) / 100; 
         totalLots += lotsBought;
      }

      for (let i = 1; i <= months; i++) {
        // 2. Hitung Perubahan Harga (Random Walk)
        // Change % = Drift + Random Volatility
        const changePct = this.randomNormal(baseDrift, this.params.volatility); 
        
        // Update Harga Saham
        const oldPrice = currentPrice;
        currentPrice = oldPrice * (1 + changePct);
        
        // Jaga harga tidak boleh 0 atau negatif (bangkrut)
        if (currentPrice < 50) currentPrice = 50; 

        // 3. DCA (Dollar Cost Averaging)
        let monthlyInvest = Number(this.params.monthly);
        let lotsAcquired = 0;
        
        if (monthlyInvest > 0) {
            lotsAcquired = (monthlyInvest / currentPrice) / 100;
            totalLots += lotsAcquired;
            totalMoneyOut += monthlyInvest;
        }

        // 4. Nilai Aset Sekarang
        // Nilai = Total Lot * 100 lembar * Harga Sekarang
        const currentPortfolioValue = totalLots * 100 * currentPrice;

        this.simulationData.push({
            month: i,
            stockPrice: currentPrice,
            changePct: changePct * 100, // Convert to scale 0-100 for display
            investment: monthlyInvest,
            lotsAcquired: lotsAcquired,
            totalLots: totalLots,
            portfolioValue: currentPortfolioValue
        });
      }

      // 5. Update Summary
      this.stockInfo.currentPrice = currentPrice;
      this.summary.totalInvested = totalMoneyOut;
      this.summary.totalLots = totalLots;
      this.summary.currentValue = totalLots * 100 * currentPrice;
      
      this.summary.totalGainLoss = this.summary.currentValue - this.summary.totalInvested;
      this.summary.isProfit = this.summary.totalGainLoss >= 0;
      
      if (this.summary.totalInvested > 0) {
         this.summary.percentageGain = ((this.summary.totalGainLoss / this.summary.totalInvested) * 100).toFixed(2);
      } else {
         this.summary.percentageGain = 0;
      }
    },

    async downloadExcel() {
      const workbook = new ExcelJS.Workbook();
      const ws = workbook.addWorksheet("Simulasi Saham DCA");

      // Styles
      const headerStyle = { font: { bold: true, color: { argb: "FFFFFFFF" } }, fill: { type: "pattern", pattern: "solid", fgColor: { argb: "FF4F46E5" } }, alignment: { horizontal: "center" } }; // Indigo color
      const currencyFmt = '"Rp "#,##0';

      // Header Info
      ws.mergeCells("A1:F1");
      ws.getCell("A1").value = "SIMULASI INVESTASI SAHAM (DCA Strategy)";
      ws.getCell("A1").font = { size: 14, bold: true };
      ws.getCell("A1").alignment = { horizontal: "center" };

      ws.addRow(["Modal Awal", this.params.initial]).getCell(2).numFmt = currencyFmt;
      ws.addRow(["Setoran Bulanan", this.params.monthly]).getCell(2).numFmt = currencyFmt;
      ws.addRow(["Durasi", this.params.years + " Tahun"]);
      ws.addRow(["Volatilitas", (this.params.volatility * 100).toFixed(0) + "%"]);
      ws.addRow([]);

      ws.addRow(["RANGKUMAN PORTOFOLIO"]).font = { bold: true };
      ws.addRow(["Total Modal", this.summary.totalInvested]).getCell(2).numFmt = currencyFmt;
      ws.addRow(["Nilai Aset Akhir", this.summary.currentValue]).getCell(2).numFmt = currencyFmt;
      const plRow = ws.addRow(["Keuntungan/Kerugian", this.summary.totalGainLoss]);
      plRow.getCell(2).numFmt = currencyFmt;
      plRow.getCell(2).font = { color: { argb: this.summary.isProfit ? "FF008000" : "FFFF0000" } };
      ws.addRow([]);

      // Table Headers
      const tableHead = ws.addRow(["Bulan", "Harga Saham", "Perubahan %", "Investasi", "Beli Lot", "Nilai Portfolio"]);
      tableHead.eachCell((cell) => { Object.assign(cell, headerStyle); });

      // Rows
      this.simulationData.forEach(row => {
          const r = ws.addRow([
              row.month,
              row.stockPrice,
              row.changePct / 100, // Excel needs decimal for percentage
              row.investment,
              row.lotsAcquired,
              row.portfolioValue
          ]);
          
          r.getCell(2).numFmt = currencyFmt;
          r.getCell(3).numFmt = '0.00%'; // Format Percent
          // Color text for change %
          r.getCell(3).font = { color: { argb: row.changePct >= 0 ? "FF008000" : "FFFF0000" } };
          
          r.getCell(4).numFmt = currencyFmt;
          r.getCell(5).numFmt = '0.00 "Lot"';
          r.getCell(6).numFmt = currencyFmt;
          r.getCell(6).font = { bold: true };
      });

      // Auto Width
      ws.columns.forEach(col => { col.width = 18; });

      const buffer = await workbook.xlsx.writeBuffer();
      saveAs(new Blob([buffer]), "Simulasi_Saham_DCA.xlsx");
    }
  }
};
</script>

<style scoped>
/* Transisi List */
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

/* Scrollbar Custom */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c7c7cc; 
    border-radius: 10px;
}

/* Smooth inputs */
.input-smooth:focus {
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); /* Indigo focus ring */
}

.number-transition {
   transition: color 0.3s ease, transform 0.3s ease;
}
</style>