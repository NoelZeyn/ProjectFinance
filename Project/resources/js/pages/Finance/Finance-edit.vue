<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 flex flex-col bg-white overflow-auto">
      <HeaderBar title="Tambah Track Finance" />
      <div class="border-b border-gray-300"></div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl shadow mt-6">
        <h3 class="text-[15px] text-[#074a5d] font-semibold mb-4">
          Update Track Finance
        </h3>

        <div class="h-[1px] w-full bg-gray-300 my-4"></div>

        <div class="flex flex-col gap-6">

          <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded shadow-sm text-sm">
            <div class="flex items-start">
              <div class="py-1">
                <svg class="fill-current h-6 w-6 text-blue-500 mr-4" xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20">
                  <path
                    d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                </svg>
              </div>
              <div>
                <p class="font-bold">Informasi Penting</p>
                <p class="text-xs sm:text-sm mt-1 text-[#074a5d] text-blue-700">
                  Untuk menjaga keakuratan saldo,
                  <b>Tipe Transaksi</b> dan <b>Tanggal</b> tidak dapat diubah pada menu ini.
                  Jika Anda perlu mengubahnya, silakan
                  <b class="text-red-600">hapus data ini</b> dan buat transaksi baru.
                </p>
              </div>
            </div>
          </div>
          <h4 class="text-[15px] font-medium text-black text-center">
            Form Update Track Finance
          </h4>

          <template v-for="field in fields" :key="field.key">
            <div class="flex flex-col gap-1">
              <div class="flex flex-col sm:flex-row gap-3">
                <label class="sm:w-[150px] font-semibold text-sm">
                  {{ field.label }}
                </label>

                <textarea v-if="field.type === 'textarea'" v-model="formData[field.key]"
                  :placeholder="field.placeholder" class="w-full p-2 border rounded-lg bg-gray-100 text-sm" />

                <select v-else-if="field.type === 'select'" v-model="formData[field.key]"
                  :disabled="field.key === 'type'" :class="[
                    'w-full p-2 border rounded-lg text-sm border-gray-300',
                    field.key === 'type' ? 'bg-gray-200 cursor-not-allowed text-gray-500' : 'bg-gray-100'
                  ]">
                  <option v-for="opt in field.options" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>

                <input v-else :type="field.type" v-model="formData[field.key]" :placeholder="field.placeholder"
                  :disabled="field.key === 'date'" :readonly="field.key === 'total'" :class="[
                    'w-full p-2 border rounded-lg text-sm',
                    (field.key === 'total' || field.key === 'date')
                      ? 'bg-gray-200 cursor-not-allowed text-gray-500' // Style untuk disable/readonly
                      : 'bg-gray-100',
                    errors[field.key] ? 'border-red-500' : 'border-gray-300'
                  ]" />
              </div>

              <span v-if="errors[field.key]" class="text-red-500 text-xs ml-[150px]">
                {{ errors[field.key] }}
              </span>
            </div>
          </template>

          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <div class="flex justify-between mt-6">
            <router-link to="/finance-main">
              <button
                class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                Kembali
              </button>
            </router-link>

            <button @click="submitTrack"
              class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
              Update Track
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import SuccessAlert from "@/components/SuccessAlert.vue";
import axios from "axios";

export default {
  name: "TrackAdd",
  components: { Sidebar, HeaderBar, SuccessAlert },

  data() {
    return {
      activeMenu: "finance-main",
      formData: {
        item: "",
        date: "",
        category: "",
        description: "",
        amount: "",
        price: "",
        total: 0,
        type: "",
        id_admin_fk: null,
      },
      errors: {},
      showSuccessAlert: false,
      successMessage: "",
      fields: [
        {
          key: "type",
          label: "Tipe Transaksi",
          type: "select",
          options: [
            { label: "Expense (Pengeluaran)", value: "expense" },
            { label: "Income (Pemasukan)", value: "income" },
          ],
          placeholder: "Pilih Tipe Transaksi",
        },
        { key: "item", label: "Nama Barang", type: "text", placeholder: "Nama Barang" },
        { key: "date", label: "Tanggal", type: "date", placeholder: "Tanggal" },
        { key: "category", label: "Kategori", type: "text", placeholder: "Kategori" },
        { key: "description", label: "Deskripsi", type: "textarea", placeholder: "Deskripsi" },
        { key: "amount", label: "Jumlah", type: "number", placeholder: "Jumlah" },
        { key: "price", label: "Harga (Rp)", type: "number", placeholder: "Harga (Rp)" },
        { key: "total", label: "Total Harga (Rp)", type: "number" }, // ✅ WAJIB ADA
      ],
    };
  },

  computed: {
    computedTotal() {
      const amount = Number(this.formData.amount);
      const price = Number(this.formData.price);
      return amount > 0 && price > 0 ? amount * price : 0;
    },
  },

  watch: {
    computedTotal(val) {
      this.formData.total = val;
    },
  },
  mounted() {
    const user = JSON.parse(localStorage.getItem("user"));
    if (user) {
      this.formData.id_admin_fk = user.id;
    }
    this.fetchTracks();
  },

  methods: {
    validateForm() {
      this.errors = {};
      if (!this.formData.item) this.errors.item = "Nama barang wajib diisi";
      if (!this.formData.amount) this.errors.amount = "Jumlah wajib diisi";
      if (!this.formData.price) this.errors.price = "Harga wajib diisi";
      return Object.keys(this.errors).length === 0;
    },

    async fetchTracks() {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(
          `http://localhost:8000/api/finance-info/${this.$route.params.id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
          }
        );

        // PERBAIKAN DISINI: Format tanggal sebelum dimasukkan ke formData
        if (response.data?.data) {
          const fetchedData = response.data.data;

          // Cek jika ada field 'date' dan formatnya panjang, kita potong ambil YYYY-MM-DD saja
          if (fetchedData.date && fetchedData.date.includes("T")) {
            fetchedData.date = fetchedData.date.split("T")[0];
          }

          this.formData = { ...this.formData, ...fetchedData };
        }
      } catch (err) {
        console.error("Gagal mengambil data finance:", err);
      }
    },
    async submitTrack() {
      if (!this.validateForm()) return;

      const user = JSON.parse(localStorage.getItem("user"));
      this.formData.id_admin_fk = user.id;

      const token = localStorage.getItem("token");

      await axios.put(`http://localhost:8000/api/finance-edit/${this.formData.id_finance}`, this.formData, {
        headers: { Authorization: `Bearer ${token}` },
      });

      this.successMessage = "Track berhasil diubah";
      this.showSuccessAlert = true;

      setTimeout(() => this.$router.push("/finance-main"), 2000);
    },
  },
};
</script>
