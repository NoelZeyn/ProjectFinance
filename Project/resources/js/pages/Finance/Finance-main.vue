<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white overflow-auto">
      <HeaderBar title="Tracking Finance" class="mt-3" />
      <div class="my-4 border-b border-gray-300"></div>

      <div class="pb-12">
        <div class="filters flex flex-wrap gap-4 mb-4">
          <div class="relative flex-1 min-w-[200px]">
            <input type="text" v-model="searchQuery" @input="onInputSearch" placeholder="Cari Nama Barang..."
              class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
            <img src="@/assets/search.svg" alt="Search"
              class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          </div>
          <div class="relative flex-1 min-w-[200px]">
            <input type="date" v-model="searchDate"
              class="w-full border border-gray-300 rounded-md py-2 pl-4 pr-4 text-sm text-gray-700" />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
          <div class="flex flex-wrap gap-2 justify-between items-center px-5 p-3 border-b border-gray-300">
            <h3 class="text-sm font-semibold text-gray-900">Data Finance</h3>

            <div class="flex flex-wrap gap-2">
              <button @click="openExportModal" class="text-sm font-semibold text-green-700 hover:text-green-900 cursor-pointer">
                Download Excel
              </button>
              
              <router-link v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'" to="/finance-add"
                class="text-sm font-semibold text-[#074a5d] no-underline hover:text-[#0066cc] hover:no-underline">
                Tambah Tracking
              </router-link>
            </div>
          </div>

          <!-- Admin/Superadmin Table -->
          <div class="overflow-x-auto">
            <table v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
              class="min-w-full table-auto border-collapse border border-gray-300">
              <thead class="bg-gray-100 text-[#7d7f81]">
                <tr>
                  <th class="p-3 border">Date</th>
                  <th class="p-3 border">Item</th>
                  <th class="p-3 border">Category</th>
                  <th class="p-3 border">Type</th>
                  <th class="p-3 border">Description</th>
                  <th class="p-3 border">Amount</th>
                  <th class="p-3 border">Price</th>
                  <th class="p-3 border">Total</th>
                  <th class="p-3 border">Aksi</th>
                </tr>
              </thead>
              <tbody class="text-sm text-gray-700">

                <template v-if="isLoading">
                  <tr v-for="n in 5" :key="n" class="animate-pulse border-b border-gray-200">
                    <td class="p-3 border">
                      <div class="h-4 bg-gray-200 rounded w-24 mx-auto"></div>
                    </td>
                    <td class="p-3 border">
                      <div class="h-4 bg-gray-200 rounded w-32 mx-auto"></div>
                    </td>
                    <td class="p-3 border">
                      <div class="h-4 bg-gray-200 rounded w-20 mx-auto"></div>
                    </td>
                    <td class="p-3 border">
                      <div class="h-4 bg-gray-200 rounded w-20 mx-auto"></div>
                    </td>
                    <td class="p-3 border">
                      <div class="h-4 bg-gray-200 rounded w-10 mx-auto"></div>
                    </td>
                    <td class="p-3 border">
                      <div class="h-4 bg-gray-200 rounded w-10 mx-auto"></div>
                    </td>
                    <td class="p-3 border">
                      <div class="h-4 bg-gray-200 rounded w-20 mx-auto ml-auto"></div>
                    </td>
                    <td class="p-3 border">
                      <div class="h-4 bg-gray-200 rounded w-24 mx-auto ml-auto"></div>
                    </td>
                    <td class="p-3 border">
                      <div class="flex justify-center gap-2">
                        <div class="h-5 w-5 bg-gray-200 rounded-full"></div>
                        <div class="h-5 w-5 bg-gray-200 rounded-full"></div>
                        <div class="h-5 w-5 bg-gray-200 rounded-full"></div>
                      </div>
                    </td>
                  </tr>
                </template>

                <template v-else>
                  <template v-for="(items, date) in groupedFinanceByDate" :key="date">

                    <tr class="hover:bg-gray-50">
                      <td class="p-3 border bg-white align-middle font-medium" :rowspan="items.length">
                        {{ formatDate(date) }}
                      </td>
                      <td class="p-3 border">{{ items[0].item }}</td>
                      <td class="p-3 border">{{ items[0].category }}</td>
                      <td class="p-3 border">{{ items[0].type }}</td>
                      <td class="p-3 border">{{ items[0].description }}</td>
                      <td class="p-3 border">{{ items[0].amount }}</td>
                      <td class="p-3 border text-right">{{ formatRupiah(items[0].price) }}</td>
                      <td class="p-3 border text-right">{{ formatRupiah(items[0].total) }}</td>
                      <td class="p-3 border">
                        <div class="flex flex-wrap justify-center items-center gap-2 sm:space-x-2">
                          <button title="Informasi" @click="navigateTo('info', items[0])"
                            class="cursor-pointer hover:opacity-70">
                            <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                          </button>
                          <button title="Edit"
                            v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                            @click="navigateTo('edit', items[0])"
                            class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                            <img :src="updateIcon" alt="Update" class="w-5 h-5 object-contain" />
                          </button>
                          <button title="Hapus"
                            v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                            @click="confirmDelete(items[0])"
                            class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                            <img :src="deleteIcon" alt="Delete" class="w-5 h-5 object-contain" />
                          </button>
                        </div>
                      </td>
                    </tr>

                    <tr v-for="(item, idx) in items.slice(1)" :key="item.id_finance || idx" class="hover:bg-gray-50">
                      <td class="p-3 border">{{ item.item }}</td>
                      <td class="p-3 border">{{ item.category }}</td>
                      <td class="p-3 border">{{ item.type }}</td>
                      <td class="p-3 border">{{ item.description }}</td>
                      <td class="p-3 border">{{ item.amount }}</td>
                      <td class="p-3 border text-right">{{ formatRupiah(item.price) }}</td>
                      <td class="p-3 border text-right">{{ formatRupiah(item.total) }}</td>
                      <td class="p-3 border">
                        <div class="flex flex-wrap justify-center items-center gap-2 sm:space-x-2">
                          <button title="Informasi" @click="navigateTo('info', item)"
                            class="cursor-pointer hover:opacity-70">
                            <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                          </button>
                          <button title="Edit"
                            v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                            @click="navigateTo('edit', item)"
                            class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                            <img :src="updateIcon" alt="Update" class="w-5 h-5 object-contain" />
                          </button>
                          <button title="Hapus"
                            v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                            @click="confirmDelete(item)"
                            class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                            <img :src="deleteIcon" alt="Delete" class="w-5 h-5 object-contain" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>

                  <tr v-if="Object.keys(groupedFinanceByDate).length === 0">
                    <td colspan="7" class="p-8 text-center text-gray-500">
                      Tidak ada data finance ditemukan.
                    </td>
                  </tr>
                </template>
              </tbody>

            </table>
          </div>

          <!-- Pagination -->
          <div class="flex justify-between items-center px-4 py-3 border-t border-gray-300 text-sm text-[#333436]">
            <button @click="prevPage" :disabled="currentPage === 1"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
              Prev
            </button>
            <span>
              Halaman {{ currentPage }} dari
              {{ tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin' ? totalPages : totalPagesUser }}
            </span>
            <button @click="nextPage"
              :disabled="currentPage === (tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin' ? totalPages : totalPagesUser)"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
    <ModalConfirm :visible="showModal" title="Konfirmasi Hapus Data"
      message="Apakah Anda yakin ingin menghapus data ini?" @cancel="cancelDelete" @confirm="deleteFinance" />
      <div v-if="showExportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex justify-center items-center">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h3 class="text-lg font-bold mb-4 text-[#074a5d] border-b pb-2">Export Laporan Excel</h3>
        
        <div class="mb-5">
          <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Jangka Waktu:</label>
          <div class="flex flex-col gap-3">
            <label class="inline-flex items-center cursor-pointer">
              <input type="radio" v-model="exportType" value="monthly" class="form-radio text-[#074a5d] focus:ring-[#074a5d]">
              <span class="ml-2 text-gray-800">Laporan Bulanan</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input type="radio" v-model="exportType" value="all" class="form-radio text-[#074a5d] focus:ring-[#074a5d]">
              <span class="ml-2 text-gray-800">Semua Data (Rekap Total)</span>
            </label>
          </div>
        </div>

        <transition name="fade">
          <div v-if="exportType === 'monthly'" class="bg-gray-50 p-3 rounded border border-gray-200 mb-4">
            <div class="mb-3">
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bulan</label>
              <select v-model="exportMonth" class="w-full border-gray-300 border p-2 rounded text-sm focus:border-[#074a5d] outline-none">
                <option v-for="(m, index) in months" :key="index" :value="index + 1">{{ m }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun</label>
              <input type="number" v-model="exportYear" class="w-full border-gray-300 border p-2 rounded text-sm focus:border-[#074a5d] outline-none" placeholder="Tahun">
            </div>
          </div>
        </transition>

        <div class="flex justify-end gap-3 mt-6">
          <button @click="showExportModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm font-medium transition">
            Batal
          </button>
          <button @click="processExport" class="px-4 py-2 bg-[#074a5d] text-white rounded hover:bg-[#053341] text-sm font-medium shadow transition flex items-center gap-2">
            <span v-if="isExporting">Memproses...</span>
            <span v-else>Download</span>
          </button>
        </div>
        </div>
        </div>
  </div>
</template>


<script>
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import ModalConfirm from "@/components/ModalConfirm.vue";
import SuccessAlert from "@/components/SuccessAlert.vue";
import informasiIcon from "@/assets/Informasi.svg";
import updateIcon from "@/assets/Edit.svg";
import deleteIcon from "@/assets/Delete.svg";
import axios from "axios";
const wb = new ExcelJS.Workbook();
const ws = wb.addWorksheet("Finance");
// This component manages the inventory of tools (ATK) in the application.
export default {
  name: "TrackingFinance",
  components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert },

  data() {
    return {
      activeMenu: "finance-main",
      searchQuery: "",
      showModal: false,
      showSuccessAlert: false,
      successMessage: "",
      financeToDelete: null,
      tingkatanOtoritas: "",
      financeList: [],
      userFinanceList: [],
      informasiIcon,
      updateIcon,
      deleteIcon,
      searchDate: "", // untuk filter tanggal
      currentPage: 1,
      itemsPerPage: 10,
      isLoading: false,
showExportModal: false,
      isExporting: false,
      exportType: 'monthly', // 'monthly' | 'all'
      exportMonth: new Date().getMonth() + 1,
      exportYear: new Date().getFullYear(),
      months: [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
      ],
    };
  },

  computed: {
    filteredFinanceList() {
      return this.financeList
        .filter(a => {
          const searchMatch =
            !this.searchQuery ||
            a.item.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            (a.keterangan && a.keterangan.toLowerCase().includes(this.searchQuery.toLowerCase()));

          return searchMatch;
        })
        .sort((a, b) => new Date(b.date) - new Date(a.date)); // ⬅ terbaru dulu
    },
groupedFinanceByDate() {
      const filtered = this.financeList.filter(a => {
        // 1. Filter Nama (Barang/Keterangan)
        const searchMatch =
          !this.searchQuery ||
          a.item.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          (a.description && a.description.toLowerCase().includes(this.searchQuery.toLowerCase())); // Note: saya ganti 'keterangan' jadi 'description' sesuai table

        // 2. Filter Date (PERBAIKAN DISINI)
        // Kita cek apakah 'a.date' mengandung string dari 'this.searchDate'
        // Ini menangani format "YYYY-MM-DD HH:mm:ss" vs "YYYY-MM-DD"
        const dateMatch = 
            !this.searchDate || 
            (a.date && a.date.toString().startsWith(this.searchDate));

        return searchMatch && dateMatch;
      });

      // Sort Tanggal (Terbaru ke Terlama)
      filtered.sort((a, b) => new Date(b.date) - new Date(a.date));

      // Grouping
      const grouped = {};
      filtered.forEach(item => {
        // Ambil hanya tanggalnya saja (YYYY-MM-DD) untuk kunci grouping
        // Supaya jam yang berbeda tetap masuk ke grup tanggal yang sama
        const dateKey = item.date.substring(0, 10); 
        
        if (!grouped[dateKey]) grouped[dateKey] = [];
        grouped[dateKey].push(item);
      });

      return grouped;
    },

    filteredUserFinanceList() {
      return this.userFinanceList
        .filter(a => {
          const searchMatch =
            !this.searchQuery ||
            a.item.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            (a.keterangan && a.keterangan.toLowerCase().includes(this.searchQuery.toLowerCase()));

          return searchMatch;
        })
        .sort((a, b) => new Date(b.date) - new Date(a.date)); // ⬅ terbaru dulu
    },


    paginatedFinanceList() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredFinanceList.slice(start, start + this.itemsPerPage);
    },
    paginatedUserFinanceList() {
      // if (!Array.isArray(this.userFinanceList)) return [];
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredUserFinanceList.slice(start, start + this.itemsPerPage);
    },

    totalPages() {
      return Math.ceil(this.filteredFinanceList.length / this.itemsPerPage);
    },

    totalPagesUser() {
      return Math.ceil(this.userFinanceList.length / this.itemsPerPage);
    }

  },

  created() {
    this.getUserInfo();
    this.fetchFinance();
    this.fetchUserFinance();
  },

  methods: {

formatDate(dateString) {
    if (!dateString) return "-";
    // dateString kemungkinan "2023-12-25" (karena substring di grouping tadi)
    // new Date("2023-12-25") hasilnya aman.
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
  },
    formatRupiah(angka) {
      if (!angka) return "-";
      return "Rp. " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    async getUserInfo() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.post("http://localhost:8000/api/me", {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.tingkatanOtoritas = res.data.tingkatan_otoritas;
      } catch (err) {
        console.error("Gagal mengambil data user:", err);
      }
    },

openExportModal() {
      this.showExportModal = true;
    },
async processExport() {
      this.isExporting = true;
      try {
        // 1. Siapkan Data (Urutkan dari yang terlama ke terbaru)
        const allDataSorted = [...this.financeList].sort((a, b) => new Date(a.date) - new Date(b.date));
        
        let filteredData = [];
        let initialBalance = 0;
        let periodName = "";

        if (this.exportType === 'all') {
            // --- LOGIKA DOWNLOAD SEMUA DATA ---
            filteredData = allDataSorted;
            initialBalance = 0; // Asumsi mulai dari 0 untuk rekap keseluruhan
            periodName = "Semua Riwayat Transaksi";
        } else {
            // --- LOGIKA DOWNLOAD BULANAN ---
            const targetMonthIdx = this.exportMonth - 1;
            const targetYear = parseInt(this.exportYear);
            periodName = `${this.months[targetMonthIdx]} ${targetYear}`;

            // Hitung Saldo Awal (Akumulasi transaksi sebelum bulan yang dipilih)
            for (const item of allDataSorted) {
                const d = new Date(item.date);
                // Jika tanggal item < tanggal awal bulan yang dipilih
                if (d.getFullYear() < targetYear || (d.getFullYear() === targetYear && d.getMonth() < targetMonthIdx)) {
                    const nominal = Number(item.total);
                    if (item.type === 'income') initialBalance += nominal;
                    else initialBalance -= nominal;
                }
                // Jika masuk periode bulan yg dipilih, masukkan ke data export
                else if (d.getFullYear() === targetYear && d.getMonth() === targetMonthIdx) {
                    filteredData.push(item);
                }
            }
        }

        if (filteredData.length === 0) {
             alert("Tidak ada data transaksi untuk diexport.");
             this.isExporting = false;
             return;
        }

        // 2. Generate Excel dengan Tampilan Baru
        await this.generateExcelOldStyle(filteredData, initialBalance, periodName);

      } catch (error) {
        console.error(error);
        alert("Gagal membuat laporan Excel.");
      } finally {
        this.isExporting = false;
        this.showExportModal = false;
      }
    },

async generateExcelOldStyle(data, initialBalance, periodTitle) {
      // 1. Grouping Data per Hari
      const dailyMap = {};
      data.forEach((item) => {
        if (!dailyMap[item.date]) dailyMap[item.date] = [];
        dailyMap[item.date].push(item);
      });

      const workbook = new ExcelJS.Workbook();
      const ws = workbook.addWorksheet("Laporan Finance", { views: [{ showGridLines: false }] });

      // --- 2. HEADER UTAMA ---
      ws.mergeCells("A1:H1");
      const title = ws.getCell("A1");
      title.value = "LAPORAN TRACKING FINANCE";
      title.font = { name: "Arial", size: 16, bold: true, color: { argb: "FF074A5D" } };
      title.alignment = { horizontal: "center", vertical: "middle" };

      ws.mergeCells("A2:H2");
      const subTitle = ws.getCell("A2");
      subTitle.value = `Periode: ${periodTitle}`;
      subTitle.font = { name: "Arial", size: 12, italic: true };
      subTitle.alignment = { horizontal: "center", vertical: "middle" };

      ws.addRow([]); // Spacer

      // --- 3. TABLE HEADER ---
      const headerRow = ws.addRow([
        "TANGGAL", "NAMA BARANG", "KATEGORI", "TIPE", 
        "DESKRIPSI", "JUMLAH", "HARGA", "TOTAL"
      ]);

      headerRow.eachCell((cell) => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" }, size: 11 };
        cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF074A5D" } };
        cell.alignment = { horizontal: "center", vertical: "middle" };
        cell.border = { top: { style: "medium" }, bottom: { style: "medium" }, left: {style:'thin'}, right: {style:'thin'} };
      });
      headerRow.height = 25;

      // Lebar Kolom
      ws.getColumn(1).width = 18; 
      ws.getColumn(2).width = 25; 
      ws.getColumn(3).width = 15; 
      ws.getColumn(4).width = 12; 
      ws.getColumn(5).width = 30; 
      ws.getColumn(6).width = 10; 
      ws.getColumn(7).width = 18; 
      ws.getColumn(8).width = 20; 

      // --- 4. SALDO AWAL ---
      const rowSaldoAwal = ws.addRow([
          "", "SALDO AWAL", "", "", "", "", "", initialBalance 
      ]);
      
      ws.mergeCells(`A${rowSaldoAwal.number}:G${rowSaldoAwal.number}`);
      
      const labelSaldoAwal = rowSaldoAwal.getCell(1);
      labelSaldoAwal.alignment = { horizontal: "right", vertical: "middle" };
      labelSaldoAwal.font = { bold: true, italic: true, size: 11, color: { argb: "FF333333" } };
      labelSaldoAwal.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFEEEEEE" } };

      const valSaldoAwal = rowSaldoAwal.getCell(8);
      valSaldoAwal.numFmt = '"Rp "#,##0';
      valSaldoAwal.font = { bold: true, size: 11 };
      valSaldoAwal.alignment = { horizontal: "right" };
      valSaldoAwal.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFEEEEEE" } };
      
      rowSaldoAwal.eachCell(cell => {
          cell.border = { bottom: { style: "thin", color: { argb: "FFCCCCCC" } } };
      });

      // --- 5. LOOP DATA ---
      let runningTotalIncome = 0;
      let runningTotalExpense = 0;
      let sortedDates = Object.keys(dailyMap).sort(); 
      let lastMonthYear = ""; 
      
      const getMonthName = (dateStr) => {
          const d = new Date(dateStr);
          const monthNames = ["JANUARI", "FEBRUARI", "MARET", "APRIL", "MEI", "JUNI", "JULI", "AGUSTUS", "SEPTEMBER", "OKTOBER", "NOVEMBER", "DESEMBER"];
          return `${monthNames[d.getMonth()]} ${d.getFullYear()}`;
      };

      sortedDates.forEach((date) => {
          // A. Header Bulan
          const currentMonthYear = getMonthName(date);
          if (currentMonthYear !== lastMonthYear) {
              const monthRow = ws.addRow([currentMonthYear.toUpperCase(), "", "", "", "", "", "", ""]);
              ws.mergeCells(`A${monthRow.number}:H${monthRow.number}`);
              
              const cellMonth = monthRow.getCell(1);
              cellMonth.font = { bold: true, size: 11, color: { argb: "FF074A5D" } };
              cellMonth.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFD1E7EB" } }; 
              cellMonth.alignment = { horizontal: "left", indent: 1 };
              cellMonth.border = { top: { style: "thin" }, bottom: { style: "thin" } };
              
              lastMonthYear = currentMonthYear;
          }

          // B. Render Data
          const items = dailyMap[date];
          const startRow = ws.lastRow.number + 1; 

          items.forEach((item) => {
              const nominal = Number(item.total);
              const isIncome = item.type === 'income';

              if(isIncome) {
                  runningTotalIncome += nominal;
              } else {
                  runningTotalExpense += nominal;
              }

              const row = ws.addRow([
                  this.formatDate(date),
                  item.item,
                  item.category,
                  item.type.toUpperCase(),
                  item.description || "-",
                  item.amount,
                  item.price,
                  nominal
              ]);

              const typeColor = isIncome ? "FF008000" : "FFCC0000"; 
              row.getCell(4).font = { color: { argb: typeColor }, bold: true };
              row.getCell(8).font = { color: { argb: typeColor } };
              
              row.getCell(7).numFmt = '"Rp "#,##0';
              row.getCell(8).numFmt = '"Rp "#,##0';

              row.eachCell((cell) => {
                  cell.border = { 
                      left: { style: "thin", color: { argb: "FFCCCCCC" } }, 
                      right: { style: "thin", color: { argb: "FFCCCCCC" } },
                      bottom: { style: "thin", color: { argb: "FFCCCCCC" } }
                  };
              });
              
              row.getCell(1).alignment = { vertical: 'middle', horizontal: 'center' }; 
              row.getCell(6).alignment = { horizontal: 'center' };
          });

          // C. Merge Tanggal
          const endRow = ws.lastRow.number;
          if (endRow >= startRow) {
              ws.mergeCells(`A${startRow}:A${endRow}`);
              const dateCell = ws.getCell(`A${startRow}`);
              dateCell.alignment = { vertical: "top", horizontal: "center" };
              dateCell.font = { bold: true };
          }
      });

      // --- 6. RANGKUMAN (REVISI STRUKTUR) ---
      ws.addRow([]); // Spacer
      
      const finalBalance = initialBalance + runningTotalIncome - runningTotalExpense;

      // Header Rangkuman (Opsional, bisa dihapus jika ingin langsung total)
      const rowHeaderSummary = ws.addRow(["RANGKUMAN KEUANGAN", "", "", "", "", "", "", ""]);
      ws.mergeCells(`A${rowHeaderSummary.number}:H${rowHeaderSummary.number}`);
      rowHeaderSummary.getCell(1).font = { bold: true, underline: true };
      
      // >>> A. TOTAL PEMASUKAN (INCOME) - Style Baru <<<
      const rowIncome = ws.addRow([
        "TOTAL PEMASUKAN (INCOME)", "", "", "", "", "", "", runningTotalIncome
      ]);
      ws.mergeCells(`A${rowIncome.number}:G${rowIncome.number}`); // Merge Label

      const lblIncome = rowIncome.getCell(1);
      lblIncome.alignment = { horizontal: "right", vertical: "middle" };
      lblIncome.font = { bold: true, color: { argb: "FF006400" } }; // Hijau Tua
      
      const valIncome = rowIncome.getCell(8);
      valIncome.numFmt = '"Rp "#,##0';
      valIncome.font = { bold: true, color: { argb: "FF006400" } };

      rowIncome.eachCell(cell => {
        cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFE8F5E9" } }; // Hijau Muda Soft
        cell.border = { bottom: { style: "thin", color: { argb: "FFCCCCCC" } } };
      });


      // >>> B. TOTAL PENGELUARAN (EXPENSE) - Style Baru <<<
      const rowExpense = ws.addRow([
        "TOTAL PENGELUARAN (EXPENSE)", "", "", "", "", "", "", runningTotalExpense
      ]);
      ws.mergeCells(`A${rowExpense.number}:G${rowExpense.number}`); // Merge Label

      const lblExpense = rowExpense.getCell(1);
      lblExpense.alignment = { horizontal: "right", vertical: "middle" };
      lblExpense.font = { bold: true, color: { argb: "FF8B0000" } }; // Merah Tua
      
      const valExpense = rowExpense.getCell(8);
      valExpense.numFmt = '"Rp "#,##0';
      valExpense.font = { bold: true, color: { argb: "FF8B0000" } };

      rowExpense.eachCell(cell => {
        cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFFFEBEE" } }; // Merah Muda Soft
        cell.border = { bottom: { style: "thin", color: { argb: "FFCCCCCC" } } };
      });

      ws.addRow([]); // Spacer Kecil

      // >>> C. SALDO AKHIR (GRAND TOTAL) <<<
      const grandRow = ws.addRow([
          "SALDO AKHIR ( Awal + Masuk - Keluar )", "", "", "", "", "", "", finalBalance
      ]);
      ws.mergeCells(`A${grandRow.number}:G${grandRow.number}`);
      grandRow.height = 30;

      const gtLabel = grandRow.getCell(1);
      gtLabel.alignment = { horizontal: "right", vertical: "middle" };
      gtLabel.font = { bold: true, size: 12, color: { argb: "FFFFFFFF" } };

      const gtValue = grandRow.getCell(8);
      gtValue.alignment = { horizontal: "right", vertical: "middle" };
      gtValue.font = { bold: true, size: 14, color: { argb: "FFFFFFFF" } };
      gtValue.numFmt = '"Rp "#,##0';

      // Warna background Grand Total (Hijau jika positif, Merah jika negatif)
      const bgColor = finalBalance >= 0 ? "FF074A5D" : "FFCC0000"; 
      grandRow.eachCell(cell => {
          cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: bgColor } };
          cell.border = { top: {style: 'medium'}, bottom: {style: 'medium'} };
      });

      // --- 7. SIMPAN FILE ---
      const buffer = await workbook.xlsx.writeBuffer();
      const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
      const safeTitle = periodTitle.replace(/[\/\s]/g, "_");
      saveAs(blob, `Laporan_Finance_${safeTitle}.xlsx`);
    },
    async fetchUserFinance() {
      try {
        const token = localStorage.getItem("token");
        const userData = JSON.parse(localStorage.getItem("user"));
        const idPenempatan = userData.id_penempatan_fk;

        const res = await axios.get(`http://localhost:8000/api/finance/${idPenempatan}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.userFinanceList = res.data.data?.[0]?.barang || [];
      } catch (err) {
        console.error("Gagal mengambil data alat berdasarkan penempatan:", err);
      }
    },
    async fetchFinance() {
      this.isLoading = true;
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/finance", {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.financeList = res.data.data;
      } catch (err) {
        console.error("Gagal mengambil data alat:", err);
      } finally {
        this.isLoading = false;
      }
    },

    updateActiveMenu(menu) {
      this.activeMenu = menu;
    },
    navigateTo(action, finance) {
      localStorage.setItem(`dataFinance${action}`, JSON.stringify(finance));
      this.$router.push(`/finance-${action}/${finance.id_finance}`);
    },
    confirmDelete(finance) {
      this.financeToDelete = finance;
      this.showModal = true;
    },
    cancelDelete() {
      this.financeToDelete = null;
      this.showModal = false;
    },
    async deleteFinance() {
      try {
        const token = localStorage.getItem("token");

        const res = await axios.delete(
          `http://localhost:8000/api/finance-delete/${this.financeToDelete.id_finance}`,
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        // ✅ Tangani berbagai jenis status respon
        if (res.data.status === "info") {
          this.successMessage = res.data.message; // Sudah dinonaktifkan sebelumnya
        } else if (res.data.status === "success") {
          this.successMessage = res.data.message; // Berhasil hapus atau nonaktif
        }

        this.showSuccessAlert = true;
        setTimeout(() => (this.showSuccessAlert = false), 2000);

        this.fetchFinance();
      } catch (err) {
        console.error("Gagal menghapus finance:", err);
      } finally {
        this.cancelDelete();
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) this.currentPage++;
    },
    prevPage() {
      if (this.currentPage > 1) this.currentPage--;
    },
    onInputSearch() {
      this.currentPage = 1;
    },
  },
};
</script>

<style scoped>
th,
td {
  padding: 8px 10px;
  /* diperkecil */
  text-align: center;
  font-size: 12px;
  /* perkecil font */
  border: 1px solid #ccc;
  word-wrap: break-word;
}
</style>
