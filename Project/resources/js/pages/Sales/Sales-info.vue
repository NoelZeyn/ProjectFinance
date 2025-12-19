<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 bg-white overflow-auto">
      <HeaderBar title="Detail Track Sales" />
      <div class="border-b border-gray-300 mb-6"></div>

      <div class="space-y-6">

        <!-- INFORMASI TRANSAKSI -->
        <Section title="Informasi Transaksi">
          <FieldText label="Invoice Number" :modelValue="formData.invoice_number" />
          <FieldText label="Tanggal" :modelValue="formData.date" />
          <FieldText label="Metode Pembayaran" :modelValue="formData.payment_method" />
          <FieldText label="Status Penjualan" :modelValue="formData.sales_status" />
        </Section>

        <!-- INFORMASI CUSTOMER -->
        <Section title="Informasi Customer">
          <FieldText label="Nama Customer" :modelValue="formData.customer_name" />
          <FieldText label="Kontak Customer" :modelValue="formData.customer_contact" />
          <FieldText Text label="Catatan" :modelValue="formData.notes" class="sm:col-span-2" />
        </Section>

        <!-- INFORMASI PRODUK -->
        <Section title="Informasi Produk">
          <FieldText label="Nama Produk" :modelValue="formData.barang_sales?.product_name" />
          <FieldText label="Kode Produk" :modelValue="formData.barang_sales?.product_code" />
          <FieldText label="Kategori" :modelValue="formData.barang_sales?.category" />
          <FieldText label="Quantity" :modelValue="formData.quantity" />
          <FieldText label="Harga (Rp)" :modelValue="formatRupiah(formData.barang_sales?.price)" />
          <FieldText label="Total (Rp)" :modelValue="formatRupiah(formData.total)" />
        </Section>

        <!-- PENGIRIMAN & PEMBAYARAN -->
        <Section title="Pengiriman & Pembayaran">
          <FieldText label="Kurir" :modelValue="formData.courier" />
          <FieldText label="Ongkir" :modelValue="formatRupiah(formData.shipping_cost)" />
          <FieldText label="Tracking Number" :modelValue="formData.tracking_number" />
          <FieldText Text label="Alamat Pengiriman" :modelValue="formData.shipping_address" class="sm:col-span-2" />
        </Section>

        <!-- BUTTON -->
        <div class="flex justify-end pt-4">
          <router-link to="/sales-main">
            <button class="bg-[#074a5d] text-white px-6 py-2 rounded-lg hover:bg-[#063843] transition">
              Kembali
            </button>
          </router-link>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import axios from "axios";
import Section from "../../components/Section.vue";
import FieldText from "../../components/FieldText.vue";

export default {
  name: "sales-main",
  components: {
    Sidebar,
    HeaderBar,
    Section,
    FieldText,
  },


  data() {
    return {
      activeMenu: "sales",
      formData: {}
    };
  },

  mounted() {
    this.fetchData();
  },

  methods: {
    async fetchData() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get(
          `http://localhost:8000/api/sales-info/${this.$route.params.id}`,
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        this.formData = res.data.data || {};
      } catch (e) {
        console.error("Gagal mengambil detail sales:", e);
      }
    },

    formatRupiah(val) {
      if (!val) return "-";
      return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
  }
};
</script>
