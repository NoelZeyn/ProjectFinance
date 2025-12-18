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
            <input type="date" v-model="searchDate" @input="onInputSearch"
              class="w-full border border-gray-300 rounded-md py-2 pl-4 pr-4 text-sm text-gray-700" />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
          <div class="flex flex-wrap gap-2 justify-between items-center px-5 p-3 border-b border-gray-300">
            <h3 class="text-sm font-semibold text-gray-900">Data Finance</h3>

            <div class="flex flex-wrap gap-2">
              <button @click="downloadExcel" class="text-sm font-semibold text-green-700 hover:text-green-900">
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
                  <th class="p-3 border">Amount</th>
                  <th class="p-3 border">Price</th>
                  <th class="p-3 border">Total</th>
                  <th class="p-3 border">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="(items, date) in groupedFinanceByDate" :key="date">
                  <tr>
                    <td class="p-3 border" :rowspan="items.length">{{ formatDate(date) }}</td>
                    <td class="p-3 border">{{ items[0].item }}</td>
                    <td class="p-3 border">{{ items[0].category }}</td>
                    <td class="p-3 border">{{ items[0].amount }}</td>
                    <td class="p-3 border">{{ formatRupiah(items[0].price) }}</td>
                    <td class="p-3 border">{{ formatRupiah(items[0].total) }}</td>
                    <td class="p-3 border">
                      <div class="flex flex-wrap justify-center items-center gap-2 sm:space-x-2">
                        <button title="Informasi" @click="navigateTo('info', items[0])"
                          class="cursor-pointer hover:opacity-70">
                          <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                        </button>
                        <button title="Edit" v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                          @click="navigateTo('edit', items[0])"
                          class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                          <img :src="updateIcon" alt="Update" class="w-5 h-5 object-contain" />
                        </button>
                        <button title="Hapus" v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                          @click="confirmDelete(items[0])"
                          class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                          <img :src="deleteIcon" alt="Delete" class="w-5 h-5 object-contain" />
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-for="(item, idx) in items.slice(1)" :key="idx">
                    <td class="p-3 border">{{ item.item }}</td>
                    <td class="p-3 border">{{ item.category }}</td>
                    <td class="p-3 border">{{ item.amount }}</td>
                    <td class="p-3 border">{{ formatRupiah(item.price) }}</td>
                    <td class="p-3 border">{{ formatRupiah(item.total) }}</td>
                    <td class="p-3 border">
                      <div class="flex flex-wrap justify-center items-center gap-2 sm:space-x-2">
                        <button title="Informasi" @click="navigateTo('info', item)"
                          class="cursor-pointer hover:opacity-70">
                          <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                        </button>
                        <button title="Edit" v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                          @click="navigateTo('edit', item)"
                          class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                          <img :src="updateIcon" alt="Update" class="w-5 h-5 object-contain" />
                        </button>
                        <button title="Hapus" v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                          @click="confirmDelete(item)"
                          class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                          <img :src="deleteIcon" alt="Delete" class="w-5 h-5 object-contain" />
                        </button>
                      </div>
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
      activeMenu: "TrackingFinance",
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
        const searchMatch =
          !this.searchQuery ||
          a.item.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          (a.keterangan && a.keterangan.toLowerCase().includes(this.searchQuery.toLowerCase()));

        const dateMatch = !this.searchDate || a.date === this.searchDate;

        return searchMatch && dateMatch;
      });

      filtered.sort((a, b) => new Date(b.date) - new Date(a.date));

      const grouped = {};
      filtered.forEach(item => {
        if (!grouped[item.date]) grouped[item.date] = [];
        grouped[item.date].push(item);
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
    async downloadExcel() {
      const targetDate = this.searchDate
        ? new Date(this.searchDate)
        : new Date();

      const targetMonth = targetDate.getMonth();
      const targetYear = targetDate.getFullYear();

      const monthlyData = this.financeList.filter(item => {
        const d = new Date(item.date);
        return d.getMonth() === targetMonth && d.getFullYear() === targetYear;
      });

      if (monthlyData.length === 0) {
        alert("Tidak ada data pada bulan ini");
        return;
      }

      // Group per hari
      const dailyMap = {};
      monthlyData.forEach(item => {
        if (!dailyMap[item.date]) dailyMap[item.date] = [];
        dailyMap[item.date].push(item);
      });

      const workbook = new ExcelJS.Workbook();
      workbook.creator = "Finance System";
      workbook.created = new Date();

      const ws = workbook.addWorksheet("Finance Bulanan", {
        views: [{ state: "frozen", ySplit: 1 }]
      });

      // Kolom
      ws.columns = [
        { header: "Tanggal", key: "tanggal", width: 22 },
        { header: "Item", key: "item", width: 28 },
        { header: "Kategori", key: "kategori", width: 18 },
        { header: "Jumlah", key: "jumlah", width: 10 },
        { header: "Harga", key: "harga", width: 16 },
        { header: "Total", key: "total", width: 18 }
      ];

      // Style Header
      ws.getRow(1).eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
        cell.alignment = { vertical: "middle", horizontal: "center" };
        cell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FF374151" } // gray-700
        };
        cell.border = {
          top: { style: "thin" },
          bottom: { style: "thin" },
          left: { style: "thin" },
          right: { style: "thin" }
        };
      });

      let grandTotal = 0;

      Object.keys(dailyMap).sort().forEach(date => {
        let dailyTotal = 0;
        const startRow = ws.rowCount + 1;

        dailyMap[date].forEach(item => {
          const total = Number(item.total || 0);
          dailyTotal += total;
          grandTotal += total;

          ws.addRow({
            tanggal: this.formatDate(date),
            item: item.item,
            kategori: item.category,
            jumlah: item.amount,
            harga: item.price,
            total
          });
        });

        // Merge tanggal per hari
        ws.mergeCells(startRow, 1, ws.rowCount, 1);
        ws.getCell(startRow, 1).alignment = {
          vertical: "middle",
          horizontal: "center"
        };

        // Total Harian
        const totalRow = ws.addRow({
          item: "TOTAL HARIAN",
          total: dailyTotal
        });

        totalRow.eachCell(cell => {
          cell.font = { bold: true };
          cell.fill = {
            type: "pattern",
            pattern: "solid",
            fgColor: { argb: "FFE5E7EB" } // gray-200
          };
        });

        ws.addRow({});
      });

      // TOTAL BULANAN
      const totalMonthRow = ws.addRow({
        item: "TOTAL BULANAN",
        total: grandTotal
      });

      totalMonthRow.eachCell(cell => {
        cell.font = { bold: true, size: 13 };
        cell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FFD1FAE5" } // green-100
        };
      });

      // Format Rupiah
      ws.eachRow((row, rowNumber) => {
        if (rowNumber > 1) {
          row.getCell(5).numFmt = '"Rp"#,##0';
          row.getCell(6).numFmt = '"Rp"#,##0';
        }
      });

      // Border data
      ws.eachRow((row, rowNumber) => {
        if (rowNumber > 1) {
          row.eachCell(cell => {
            cell.border = {
              top: { style: "thin" },
              bottom: { style: "thin" },
              left: { style: "thin" },
              right: { style: "thin" }
            };
          });
        }
      });

      const buffer = await workbook.xlsx.writeBuffer();
      const blob = new Blob([buffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
      });

      const monthName = targetDate.toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric"
      });

      saveAs(blob, `Finance_${monthName}.xlsx`);
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
      this.isTableLoading = true;
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/finance", {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.financeList = res.data.data;
      } catch (err) {
        console.error("Gagal mengambil data alat:", err);
      } finally {
        this.isTableLoading = false;
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
