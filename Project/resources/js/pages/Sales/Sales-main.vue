<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white overflow-auto">
      <HeaderBar title="Tracking Sales" class="mt-3" />
      <div class="my-4 border-b border-gray-300"></div>

      <div class="pb-12">
        <div class="filters flex flex-wrap gap-4 mb-4">
          <div class="relative flex-1 min-w-[200px]">
            <input type="text" v-model="searchProduct" @input="onInputSearch" placeholder="Cari Nama Barang..."
              class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
            <img src="@/assets/search.svg" alt="Search"
              class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          </div>
          <div class="relative flex-1 min-w-[200px]">
            <input type="text" v-model="searchCustomer" @input="onInputSearch" placeholder="Cari Nama Customer..."
              class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
            <img src="@/assets/search.svg" alt="Search"
              class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          </div>
          <div class="relative flex-1 min-w-[200px]">
            <input type="date" v-model="searchDate" @input="onInputSearch"
              class="w-full border border-gray-300 rounded-md py-2 pl-4 pr-4 text-sm text-gray-700" />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
          <div class="flex flex-wrap gap-2 justify-between items-center px-5 p-3 border-b border-gray-300">
            <h3 class="text-sm font-semibold text-gray-900">Data Sales</h3>

            <div class="flex flex-wrap gap-2">
              <button @click="downloadExcel"
                class="text-sm font-semibold text-green-700 hover:text-green-900 hover:no-underline cursor-pointer">
                Download Excel
              </button>
              <router-link v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'" to="/sales-add"
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
                  <th class="p-3 border">Customer</th>
                  <th class="p-3 border">Quantity</th>
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
                      <div class="h-4 bg-gray-200 rounded w-8 mx-auto"></div>
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
                  <template v-for="dateGroup in structuredSalesData" :key="dateGroup.date">

                    <template v-for="(itemGroup, itemIdx) in dateGroup.items" :key="itemGroup.name">

                      <tr v-for="(sale, saleIdx) in itemGroup.sales" :key="sale.id_sales" class="hover:bg-gray-50">

                        <td v-if="itemIdx === 0 && saleIdx === 0" class="p-3 border bg-white align-middle font-medium"
                          :rowspan="dateGroup.totalRows">
                          {{ formatDate(dateGroup.date) }}
                        </td>

                        <td v-if="saleIdx === 0" class="p-3 border bg-white align-middle font-medium"
                          :rowspan="itemGroup.count">
                          {{ itemGroup.name }}
                        </td>

                        <td class="p-3 border">{{ sale.customer_name }}</td>
                        <td class="p-3 border text-center">{{ sale.quantity }}</td>
                        <td class="p-3 border text-right">{{ formatRupiah(sale.barang_sales?.price) }}</td>
                        <td class="p-3 border text-right">{{ formatRupiah(sale.total) }}</td>

                        <td class="p-3 border text-center">
                          <div class="flex flex-wrap justify-center items-center gap-2">
                            <button title="Informasi" @click="navigateTo('info', sale)" class="hover:opacity-70">
                              <img :src="informasiIcon" alt="Info" class="w-5 h-5 cursor-pointer" />
                            </button>
                            <button title="Edit"
                              v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                              @click="navigateTo('edit', sale)" class="hover:opacity-70 pl-2 border-l border-gray-300">
                              <img :src="updateIcon" alt="Edit" class="w-5 h-5 cursor-pointer" />
                            </button>
                            <button title="Hapus"
                              v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                              @click="confirmDelete(sale)" class="hover:opacity-70 pl-2 border-l border-gray-300">
                              <img :src="deleteIcon" alt="Delete" class="w-5 h-5 cursor-pointer" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    </template>
                  </template>

                  <tr v-if="structuredSalesData.length === 0">
                    <td colspan="7" class="p-8 text-center text-gray-500">
                      Tidak ada data yang ditemukan.
                    </td>
                  </tr>
                </template>

              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="flex justify-between items-center px-4 py-3 border-t border-gray-300 text-sm text-[#333436]">
            <button @click="prevPage" :disabled="currentPage === 1"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 cursor-pointer">
              Prev
            </button>
            <span>
              Halaman {{ currentPage }} dari
              {{ tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin' ? totalPages : totalPagesUser }}
            </span>
            <button @click="nextPage"
              :disabled="currentPage === (tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin' ? totalPages : totalPagesUser)"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 cursor-pointer">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
    <ModalConfirm :visible="showModal" title="Konfirmasi Hapus Data"
      message="Apakah Anda yakin ingin menghapus data ini?" @cancel="cancelDelete" @confirm="deleteSales" />
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
import Chart from 'chart.js/auto'; // Import Chart.js
export default {
  name: "TrackingSales",
  components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert },

  data() {
    return {
      activeMenu: "sales-main",
      showModal: false,
      showSuccessAlert: false,
      successMessage: "",
      salesToDelete: null,
      tingkatanOtoritas: "",
      salesList: [],
      userSalesList: [],
      informasiIcon,
      updateIcon,
      deleteIcon,
      searchProduct: "",
      searchCustomer: "",
      searchDate: "",
      currentPage: 1,
      salesPerPage: 10,
      isLoading: false,
    };
  },

  computed: {
    // 1. Filter dan Sort Data Mentah
    filteredAndSortedSales() {
      // Filter logic (sama seperti sebelumnya)
      const filtered = this.salesList.filter(s => {
        const productMatch = !this.searchProduct || s.barang_sales?.product_name?.toLowerCase().includes(this.searchProduct.toLowerCase());
        const customerMatch = !this.searchCustomer || s.customer_name?.toLowerCase().includes(this.searchCustomer.toLowerCase());
        const dateMatch = !this.searchDate || s.date === this.searchDate;
        return productMatch && customerMatch && dateMatch;
      });

      // Sorting Multi-level: Date (Desc) -> Item (Asc) -> Customer (Asc)
      return filtered.sort((a, b) => {
        // Sort by Date Desc
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        if (dateA > dateB) return -1;
        if (dateA < dateB) return 1;

        // Sort by Item Name Asc
        const itemA = (a.barang_sales?.product_name || "").toLowerCase();
        const itemB = (b.barang_sales?.product_name || "").toLowerCase();
        if (itemA < itemB) return -1;
        if (itemA > itemB) return 1;

        // Sort by Customer Name Asc
        const custA = (a.customer_name || "").toLowerCase();
        const custB = (b.customer_name || "").toLowerCase();
        if (custA < custB) return -1;
        if (custA > custB) return 1;

        return 0;
      });
    },

    // 2. Ambil data untuk halaman saat ini (Paginasi)
    paginatedSales() {
      const start = (this.currentPage - 1) * this.salesPerPage;
      return this.filteredAndSortedSales.slice(start, start + this.salesPerPage);
    },

    // 3. Struktur Data untuk Tampilan (Grouping Logic)
    structuredSalesData() {
      const groups = {};

      // Kelompokkan data halaman ini
      this.paginatedSales.forEach(sale => {
        const dateKey = sale.date;
        const itemKey = sale.barang_sales?.product_name || "Tanpa Nama Barang";

        if (!groups[dateKey]) groups[dateKey] = {};
        if (!groups[dateKey][itemKey]) groups[dateKey][itemKey] = [];

        groups[dateKey][itemKey].push(sale);
      });

      // Transform object ke Array untuk loop di Vue Template
      // Hasil: [{ date: '...', totalRows: 5, items: [{ name: '...', count: 2, sales: [] }] }]
      return Object.keys(groups).map(date => {
        const itemsObj = groups[date];

        // Map Items dalam Date tersebut
        const itemsArray = Object.keys(itemsObj).map(itemName => {
          return {
            name: itemName,
            sales: itemsObj[itemName],
            count: itemsObj[itemName].length // Rowspan untuk Item
          };
        });

        // Hitung total baris untuk Rowspan Date
        const totalDateRows = itemsArray.reduce((sum, item) => sum + item.count, 0);

        return {
          date: date,
          items: itemsArray,
          totalRows: totalDateRows // Rowspan untuk Date
        };
      }).sort((a, b) => new Date(b.date) - new Date(a.date)); // Pastikan urutan tanggal tetap Desc
    },

    totalPages() {
      return Math.ceil(this.filteredAndSortedSales.length / this.salesPerPage);
    },

    totalPagesUser() {
      return Math.ceil(this.userSalesList.length / this.salesPerPage);
    }
  },

  created() {
    this.getUserInfo();
    this.fetchSales();
    this.fetchUserSales();
  },

  methods: {
    /* ======================
       CORE FINANCE HELPERS
    ====================== */
    getPrice(sales) {
      return Number(sales.barang_sales?.price || 0);
    },

    getTotal(sales) {
      return this.getPrice(sales) * Number(sales.quantity || 0);
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric"
      });
    },

    formatRupiah(val) {
      if (!val) return "-";
      return "Rp. " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },

    styleSubtotal(row, color = "FFE5E7EB", bold = true) {
      if (!row || typeof row.eachCell !== "function") return;

      row.eachCell(cell => {
        cell.font = { bold };
        cell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: color }
        };
        cell.border = {
          top: { style: "thin" },
          bottom: { style: "thin" },
          left: { style: "thin" },
          right: { style: "thin" }
        };
      });
    },

    /* ======================
       DOWNLOAD EXCEL
    ====================== */
    async generateChartImage(labels, dataValues) {
      return new Promise((resolve) => {
        // 1. Buat elemen canvas sementara (tidak ditampilkan di layar)
        const canvas = document.createElement('canvas');
        canvas.width = 800;
        canvas.height = 400;
        const ctx = canvas.getContext('2d');

        // 2. Render Chart
        const myChart = new Chart(ctx, {
          type: 'bar', // Grafik Batang
          data: {
            labels: labels,
            datasets: [{
              label: 'Total Penjualan (Rp)',
              data: dataValues,
              backgroundColor: '#203764', // Warna Biru Navy (sesuai tema)
              borderColor: '#203764',
              borderWidth: 1
            }]
          },
          options: {
            responsive: false,
            animation: false, // Matikan animasi agar render instan
            plugins: {
              legend: { display: false },
              title: {
                display: true,
                text: 'GRAFIK PENJUALAN HARIAN',
                font: { size: 18, weight: 'bold' }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function (value) {
                    return 'Rp ' + value / 1000 + 'k'; // Format sumbu Y
                  }
                }
              }
            }
          }
        });

        // 3. Convert ke Image Base64 setelah render selesai
        setTimeout(() => {
          const imageBase64 = canvas.toDataURL('image/png');
          myChart.destroy(); // Bersihkan memori
          resolve(imageBase64);
        }, 500); // Beri jeda sedikit agar canvas ter-render sempurna
      });
    },

    // --- MAIN: Download Excel ---
    async downloadExcel() {
      if (!this.salesList.length) {
        alert("Tidak ada data untuk diunduh.");
        return;
      }

      const workbook = new ExcelJS.Workbook();
      workbook.creator = "System Admin";
      workbook.created = new Date();

      const ws = workbook.addWorksheet("Laporan Harian");

      // --- 1. SETUP DATA UNTUK CHART ---
      // Kita perlu mengelompokkan data dulu untuk grafik (Total per Tanggal)
      const chartMap = {};
      this.salesList.forEach(s => {
        const dateStr = this.formatDate(s.date); // "25 Oktober 2023"
        const total = Number(s.barang_sales?.price || 0) * Number(s.quantity || 0);
        chartMap[dateStr] = (chartMap[dateStr] || 0) + total;
      });

      const chartLabels = Object.keys(chartMap).sort((a, b) => new Date(a) - new Date(b)); // Urutkan tanggal
      const chartData = chartLabels.map(label => chartMap[label]);

      // Generate Gambar Chart
      const chartImageBase64 = await this.generateChartImage(chartLabels, chartData);

      // --- 2. SETUP KOLOM EXCEL ---
      ws.columns = [
        { key: "indent", width: 3 },     // A
        { key: "main", width: 25 },      // B
        { key: "details", width: 25 },   // C
        { key: "qty", width: 10 },       // D
        { key: "price", width: 18 },     // E
        { key: "total", width: 20 },     // F
        { key: "spacer", width: 5 },     // G (Jarak antara tabel dan grafik)
      ];

      // --- 3. TEMPEL CHART KE EXCEL ---
      // Tambahkan gambar ke workbook
      const imageId = workbook.addImage({
        base64: chartImageBase64,
        extension: 'png',
      });

      // Posisikan gambar di kolom H (sebelah kanan tabel)
      // tl: { col: 7, row: 3 } artinya mulai di Kolom H (index 7), Baris 4 (index 3)
      ws.addImage(imageId, {
        tl: { col: 7, row: 3 },
        ext: { width: 500, height: 300 } // Ukuran gambar
      });

      // --- 4. STYLE & ISI DATA TABEL (Sama seperti sebelumnya) ---
      ws.mergeCells("A1:F1");
      const title = ws.getCell("A1");
      title.value = "LAPORAN PENJUALAN PER PERIODE";
      title.font = { name: "Arial", size: 14, bold: true, color: { argb: "FFFFFFFF" } };
      title.alignment = { horizontal: "center", vertical: "middle" };
      title.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF203764" } };

      ws.mergeCells("A2:F2");
      const subtitle = ws.getCell("A2");
      subtitle.value = `Dicetak pada: ${new Date().toLocaleString("id-ID")}`;
      subtitle.font = { italic: true, size: 10 };
      subtitle.alignment = { horizontal: "center" };

      ws.addRow([]);

      const headerRow = ws.addRow(["", "CUSTOMER / ITEM", "INVOICE", "QTY", "HARGA", "TOTAL"]);
      headerRow.height = 24;
      headerRow.eachCell((cell, colNumber) => {
        if (colNumber > 0 && colNumber < 7) { // Hanya kolom tabel (A-F)
          cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF4472C4" } };
          cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
          cell.alignment = { horizontal: "center", vertical: "middle" };
        }
      });

      // ... LOGIKA LOOPING DATA SAMA SEPERTI KODE SEBELUMNYA ...
      const grouped = {};
      const sortedData = [...this.salesList].sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        if (dateA > dateB) return -1;
        if (dateA < dateB) return 1;
        if (a.barang_sales.product_name < b.barang_sales.product_name) return -1;
        return 0;
      });

      sortedData.forEach(s => {
        const d = s.date;
        const p = s.barang_sales?.product_name || "Tanpa Nama";
        if (!grouped[d]) grouped[d] = {};
        if (!grouped[d][p]) grouped[d][p] = [];
        grouped[d][p].push(s);
      });

      const curFmt = '_("Rp"* #,##0_);_("Rp"* (#,##0);_("Rp"* "-"_);_(@_)';
      let grandTotal = 0;

      for (const dateKey of Object.keys(grouped)) {
        const productsObj = grouped[dateKey];
        let dateTotal = 0;

        const rowDate = ws.addRow([null, `TANGGAL: ${this.formatDate(dateKey).toUpperCase()}`, "", "", "", ""]);
        ws.mergeCells(`B${rowDate.number}:F${rowDate.number}`);
        rowDate.getCell(2).font = { bold: true, size: 11 };
        rowDate.getCell(2).fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFD9E1F2" } };
        rowDate.getCell(2).border = { top: { style: "thin" }, bottom: { style: "thin" } };

        for (const prodKey of Object.keys(productsObj)) {
          const salesArr = productsObj[prodKey];
          let prodTotal = 0;
          let prodQty = 0;

          const rowProd = ws.addRow([null, `📦 ${prodKey}`, "", "", "", ""]);
          ws.mergeCells(`B${rowProd.number}:F${rowProd.number}`);
          rowProd.getCell(2).font = { bold: true, color: { argb: "FF203764" } };
          rowProd.getCell(2).alignment = { indent: 1 };

          salesArr.forEach(sale => {
            const price = Number(sale.barang_sales?.price || 0);
            const qty = Number(sale.quantity || 0);
            const total = price * qty;
            prodTotal += total;
            prodQty += qty;

            const rowItem = ws.addRow([null, sale.customer_name, sale.invoice_number || "-", qty, price, total]);
            rowItem.getCell(2).alignment = { indent: 3 };
            rowItem.getCell(4).alignment = { horizontal: "center" };
            rowItem.getCell(5).numFmt = curFmt;
            rowItem.getCell(6).numFmt = curFmt;
          });

          const rowSubProd = ws.addRow([null, "", "Subtotal Item", prodQty, "", prodTotal]);
          rowSubProd.font = { italic: true, size: 9, color: { argb: "FF555555" } };
          rowSubProd.getCell(3).alignment = { horizontal: "right" };
          rowSubProd.getCell(4).alignment = { horizontal: "center" };
          rowSubProd.getCell(6).numFmt = curFmt;
          rowSubProd.getCell(6).border = { top: { style: "dashed" } };
          dateTotal += prodTotal;
        }

        const rowSubDate = ws.addRow([null, "", `TOTAL ${this.formatDate(dateKey)}`, "", "", dateTotal]);
        rowSubDate.font = { bold: true };
        rowSubDate.getCell(3).alignment = { horizontal: "right" };
        rowSubDate.getCell(6).numFmt = curFmt;
        rowSubDate.getCell(6).fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFEDEDED" } };
        rowSubDate.getCell(6).border = { top: { style: "thin" }, bottom: { style: "thin" } };
        grandTotal += dateTotal;
        ws.addRow([]);
      }

      const rowGrand = ws.addRow([null, "", "GRAND TOTAL PENJUALAN", "", "", grandTotal]);
      ws.mergeCells(`C${rowGrand.number}:E${rowGrand.number}`);
      rowGrand.height = 30;
      const valCell = rowGrand.getCell(6);
      rowGrand.getCell(3).alignment = { horizontal: "right", vertical: "middle" };
      rowGrand.getCell(3).font = { bold: true, size: 12 };
      valCell.numFmt = curFmt;
      valCell.font = { bold: true, size: 12, color: { argb: "FFFFFFFF" } };
      valCell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF203764" } };
      valCell.alignment = { horizontal: "right", vertical: "middle" };

      // --- 5. FINISH ---
      const buffer = await workbook.xlsx.writeBuffer();
      saveAs(
        new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }),
        "Laporan_Sales_Dengan_Grafik.xlsx"
      );
    },
    /* ======================
       API & ACTIONS
    ====================== */
    async getUserInfo() {
      const token = localStorage.getItem("token");
      const res = await axios.post("http://localhost:8000/api/me", {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
      this.tingkatanOtoritas = res.data.tingkatan_otoritas;
    },

    async fetchSales() {
      this.isLoading = true; // Mulai Loading
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/sales", {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.salesList = res.data.data;
      } catch (error) {
        console.error("Gagal mengambil data:", error);
      } finally {
        setTimeout(() => {
          this.isLoading = false;
        }, 500);
      }
    },

    async fetchUserSales() {
      const token = localStorage.getItem("token");
      const user = JSON.parse(localStorage.getItem("user"));
      const res = await axios.get(`http://localhost:8000/api/sales/${user.id_penempatan_fk}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      this.userSalesList = res.data.data?.[0]?.barang || [];
    },

    navigateTo(action, sales) {
      localStorage.setItem(`dataSales${action}`, JSON.stringify(sales));
      this.$router.push(`/sales-${action}/${sales.id_sales}`);
    },

    confirmDelete(sales) {
      this.salesToDelete = sales;
      this.showModal = true;
    },

    cancelDelete() {
      this.salesToDelete = null;
      this.showModal = false;
    },

    async deleteSales() {
      const token = localStorage.getItem("token");
      const res = await axios.delete(
        `http://localhost:8000/api/sales-delete/${this.salesToDelete.id_sales}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      this.successMessage = res.data.message;
      this.showSuccessAlert = true;
      setTimeout(() => (this.showSuccessAlert = false), 2000);
      this.fetchSales();
      this.cancelDelete();
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

    updateActiveMenu(menu) {
      this.activeMenu = menu;
    }
  }
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
