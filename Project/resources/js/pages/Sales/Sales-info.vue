<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 bg-white overflow-auto">
      <HeaderBar title="Formulir Tambah Sales" />
      <div class="border-b border-gray-300 mb-6"></div>

      <div class="space-y-6">

        <!-- INFORMASI TRANSAKSI -->
        <Section title="Informasi Transaksi">
          <FieldText label="Invoice Number" mode="view" v-model="formData.invoice_number" />
          <FieldText label="Tanggal" type="date" mode="view" v-model="formData.date" required />
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-gray-700">
              Metode Pembayaran <span class="text-red-500">*</span>
            </label>

            <FieldText mode="view" v-model="formData.payment_method" />
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-gray-700">
              Status Penjualan <span class="text-red-500">*</span>
            </label>
            <FieldText mode="view" v-model="formData.payment_status" />
          </div>
        </Section>

        <!-- INFORMASI CUSTOMER -->
        <Section title="Informasi Customer">
          <FieldText label="Nama Customer" mode="view" v-model="formData.customer_name" required />
          <FieldText label="Kontak Customer" mode="view" v-model="formData.customer_contact" />
          <FieldText label="Catatan" mode="view" v-model="formData.notes" class="sm:col-span-2" />
        </Section>

        <!-- INFORMASI PRODUK -->
        <Section title="Informasi Produk">

          <!-- PILIH BARANG -->
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-gray-700">
              Barang Sales <span class="text-red-500">*</span>
            </label>

            <select v-model="selectedBarangId" @change="onBarangChange" class="p-2 border rounded-lg text-sm">
              <option value="">-- Pilih Barang --</option>
              <option v-for="barang in barangSales" :key="barang.id_barang_sales" :value="barang.id_barang_sales">
                {{ barang.product_name }}
              </option>
            </select>

          </div>

          <FieldText label="Kode Produk" :modelValue="displayBarang.product_code" mode="view" />
          <FieldText label="Kategori" :modelValue="displayBarang.category" mode="view" />
          <FieldText label="Quantity" type="number" mode="view" v-model="formData.quantity" required />
          <FieldText label="Harga (Rp)" :modelValue="formatRupiah(displayBarang.price)" mode="view" />
          <FieldText label="Total (Rp)" :modelValue="formatRupiah(calculatedTotal)" mode="view" />
        </Section>

        <!-- PENGIRIMAN & PEMBAYARAN -->
        <Section title="Pengiriman & Pembayaran">
          <FieldText label="Kurir" mode="view" v-model="formData.courier" />
          <FieldText label="Ongkir" type="number" mode="view" v-model="formData.shipping_cost" />
          <FieldText label="Tracking Number" mode="view" v-model="formData.tracking_number" />
          <FieldText label="Alamat Pengiriman" mode="view" v-model="formData.shipping_address" class="sm:col-span-2" />
        </Section>

        <!-- BUTTON -->
        <div class="flex justify-between mt-6">
          <router-link to="/sales-main">
            <button
              class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
              Kembali
            </button>
          </router-link>

          <button @click="submitData"
            class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
            Simpan Sales
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import Section from "@/components/Section.vue";
import FieldText from "@/components/FieldText.vue";
import axios from "axios";

export default {
  name: "SalesAdd",
  components: {
    Sidebar,
    HeaderBar,
    Section,
    FieldText,
  },

  data() {
    return {
      activeMenu: "sales-main",
      barangSales: [],
      selectedBarangId: "",
      formData: {
        invoice_number: "",
        date: "",
        payment_method: "",
        payment_status: "",
        customer_name: "",
        customer_contact: "",
        notes: "",

        id_barang_sales: "",
        quantity: 1,

        courier: "",
        shipping_cost: 0,
        tracking_number: "",
        shipping_address: ""
      },
      displayBarang: {
        product_code: "",
        category: "",
        price: 0
      }
    };
  },

  mounted() {
    this.fetchBarangSales();
    this.fetchData();
  },

  computed: {
    calculatedTotal() {
      return Number(this.formData.quantity || 0) *
        Number(this.displayBarang.price || 0);
    }
  }
  ,

  methods: {
    async fetchData() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get(
          `http://localhost:8000/api/sales-info/${this.$route.params.id}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        const data = res.data.data;

        this.formData = data;

        // ⬇️ INI PENTING
        this.selectedBarangId = data.id_barang_sales;

        this.$nextTick(() => {
          this.onBarangChange();
        });

      } catch (e) {
        console.error("Gagal mengambil detail sales:", e);
      }
    },


    async fetchBarangSales() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/barang-sales", {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.barangSales = res.data.data;
      } catch (err) {
        console.error(err);
      }
    },
    onBarangChange() {
      const barang = this.barangSales.find(
        b => b.id_barang_sales === this.selectedBarangId
      );

      if (!barang) return;

      // FK untuk backend
      this.formData.id_barang_sales = barang.id_barang_sales;

      // DISPLAY ONLY
      this.displayBarang.product_code = barang.product_code;
      this.displayBarang.category = barang.category;
      this.displayBarang.price = barang.price;
    },
    async submitData() {
      try {
        const token = localStorage.getItem("token");

        await axios.post(
          "http://localhost:8000/api/sales",
          {
            ...this.formData,
            total_payment: this.calculatedTotal // ✅ SESUAI MIGRATION
          },
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        alert("Data sales berhasil disimpan.");
        this.$router.push("/sales-main");

      } catch (err) {
        console.error(err.response?.data || err);
        alert(err.response?.data?.message || "Gagal menyimpan data sales.");
      }
    },

    formatRupiah(val) {
      if (!val) return "-";
      return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
  }
};
</script>
