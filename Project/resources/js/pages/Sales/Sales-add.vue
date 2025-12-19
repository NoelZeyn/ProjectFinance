<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 bg-white overflow-auto">
      <HeaderBar title="Formulir Tambah Sales" />
      <div class="border-b border-gray-300 mb-6"></div>

      <div class="space-y-6">

        <!-- INFORMASI TRANSAKSI -->
        <Section title="Informasi Transaksi">
          <FieldText label="Invoice Number" mode="view" />
          <FieldText label="Tanggal" type="date" mode="edit" v-model="formData.date" required />
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-gray-700">
              Metode Pembayaran <span class="text-red-500">*</span>
            </label>

            <select v-model="formData.payment_method" required
              class="p-2 border rounded-lg text-sm focus:ring-1 focus:ring-[#074a5d]">
              <option value="">-- Pilih Metode Pembayaran --</option>
              <option value="cash">Cash</option>
              <option value="cashless">Cashless</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-gray-700">
              Status Penjualan <span class="text-red-500">*</span>
            </label>

            <select v-model="formData.payment_method" required
              class="p-2 border rounded-lg text-sm focus:ring-1 focus:ring-[#074a5d]">
              <option value="">-- Pilih Status Penjualan --</option>
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="processing">Processing</option>
              <option value="shipped">Shipped</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <!-- <FieldText label="Status Penjualan" mode="edit" v-model="formData.sales_status" required /> -->
        </Section>

        <!-- INFORMASI CUSTOMER -->
        <Section title="Informasi Customer">
          <FieldText label="Nama Customer" mode="edit" v-model="formData.customer_name" required />
          <FieldText label="Kontak Customer" mode="edit" v-model="formData.customer_contact" />
          <FieldText label="Catatan" mode="edit" v-model="formData.notes" class="sm:col-span-2" />
        </Section>

        <!-- INFORMASI PRODUK -->
        <Section title="Informasi Produk">

          <!-- PILIH BARANG -->
          <div class="flex flex-col gap-1">
            <label class="text-sm font-semibold text-gray-700">
              Barang Sales <span class="text-red-500">*</span>
            </label>

            <select v-model="selectedBarangId" @change="onBarangChange"
              class="p-2 border rounded-lg text-sm focus:ring-1 focus:ring-[#074a5d]">
              <option value="">-- Pilih Barang --</option>
              <option v-for="barang in barangSales" :key="barang.id" :value="barang.id">
                {{ barang.product_name }}
              </option>
            </select>
          </div>

          <FieldText label="Kode Produk" :modelValue="formData.product_code" mode="view" />
          <FieldText label="Kategori" :modelValue="formData.category" mode="view" />
          <FieldText label="Quantity" type="number" mode="edit" v-model="formData.quantity" required />
          <FieldText label="Harga (Rp)" :modelValue="formatRupiah(formData.price)" mode="view" />
          <FieldText label="Total (Rp)" :modelValue="formatRupiah(calculatedTotal)" mode="view" />
        </Section>

        <!-- PENGIRIMAN & PEMBAYARAN -->
        <Section title="Pengiriman & Pembayaran">
          <FieldText label="Kurir" mode="edit" v-model="formData.courier" />
          <FieldText label="Ongkir" type="number" mode="edit" v-model="formData.shipping_cost" />
          <FieldText label="Tracking Number" mode="edit" v-model="formData.tracking_number" />
          <FieldText label="Alamat Pengiriman" mode="edit" v-model="formData.shipping_address" class="sm:col-span-2" />
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
        date: "",
        payment_method: "",
        sales_status: "",
        customer_name: "",
        customer_contact: "",
        notes: "",
        product_code: "",
        category: "",
        quantity: 0,
        price: 0,
        courier: "",
        shipping_cost: 0,
        tracking_number: "",
        shipping_address: ""
      }
    };
  },

  mounted() {
    this.fetchBarangSales();
  },

  computed: {
    calculatedTotal() {
      return Number(this.formData.quantity || 0) *
        Number(this.formData.price || 0);
    }
  },

  methods: {
    async fetchBarangSales() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/barang-sales", {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.barangSales = res.data.data;
      } catch (err) {
        console.error("Gagal ambil barang sales:", err);
      }
    },

    onBarangChange() {
      const barang = this.barangSales.find(
        b => b.id === this.selectedBarangId
      );

      if (!barang) return;

      this.formData.product_code = barang.product_code;
      this.formData.category = barang.category;
      this.formData.price = barang.price;
    },

    async submitData() {
      try {
        const token = localStorage.getItem("token");

        await axios.post(
          "http://localhost:8000/api/sales-add",
          {
            ...this.formData,
            total: this.calculatedTotal
          },
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        alert("Data sales berhasil disimpan.");
      } catch (err) {
        console.error(err);
        alert("Gagal menyimpan data sales.");
      }
    },

    formatRupiah(val) {
      if (!val) return "-";
      return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
  }
};
</script>
