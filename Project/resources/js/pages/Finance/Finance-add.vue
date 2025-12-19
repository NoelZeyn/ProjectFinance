<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 flex flex-col bg-white overflow-auto">
      <HeaderBar title="Tambah Track Finance" />
      <div class="border-b border-gray-300"></div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl shadow mt-6">
        <h3 class="text-[15px] text-[#074a5d] font-semibold mb-4">
          Tambah Track Finance
        </h3>

        <div class="h-[1px] w-full bg-gray-300 my-4"></div>

        <div class="flex flex-col gap-6">
          <h4 class="text-[15px] font-medium text-black text-center">
            Form Tambah Track Finance
          </h4>

          <template v-for="field in fields" :key="field.key">
            <div class="flex flex-col gap-1">
              <div class="flex flex-col sm:flex-row gap-3">
                <label class="sm:w-[150px] font-semibold text-sm">
                  {{ field.label }}
                </label>

                <!-- TEXTAREA -->
                <textarea v-if="field.type === 'textarea'" v-model="formData[field.key]"
                  :placeholder="field.placeholder"
                  class="w-full p-2 border rounded-lg bg-gray-100 text-sm border-gray-300" />
<select
  v-else-if="field.type === 'select'"
  v-model="formData[field.key]"
  class="w-full p-2 border rounded-lg bg-gray-100 text-sm border-gray-300"
>
  <option
    v-for="opt in field.options"
    :key="opt.value"
    :value="opt.value"
  >
    {{ opt.label }}
  </option>
</select>
                <!-- INPUT -->
                <input v-else :type="field.type" v-model="formData[field.key]" :placeholder="field.placeholder"
                  :readonly="field.key === 'total'" :class="[
                    'w-full p-2 border rounded-lg text-sm',
                    field.key === 'total'
                      ? 'bg-gray-200 cursor-not-allowed'
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
              Tambahkan Track
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

  methods: {
    validateForm() {
      this.errors = {};
      if (!this.formData.item) this.errors.item = "Nama barang wajib diisi";
      if (!this.formData.amount) this.errors.amount = "Jumlah wajib diisi";
      if (!this.formData.price) this.errors.price = "Harga wajib diisi";
      return Object.keys(this.errors).length === 0;
    },

    async submitTrack() {
      if (!this.validateForm()) return;

      const user = JSON.parse(localStorage.getItem("user"));
      this.formData.id_admin_fk = user.id;

      const token = localStorage.getItem("token");

      await axios.post("http://localhost:8000/api/finance", this.formData, {
        headers: { Authorization: `Bearer ${token}` },
      });

      this.successMessage = "Track berhasil ditambahkan";
      this.showSuccessAlert = true;

      setTimeout(() => this.$router.push("/finance-main"), 2000);
    },
  },
};
</script>
