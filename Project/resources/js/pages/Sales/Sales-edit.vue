<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 bg-white overflow-auto">
      <HeaderBar title="Detail Track Sales" />
      <div class="border-b border-gray-300 mb-6"></div>

      <div class="space-y-6">

        <!-- INFORMASI TRANSAKSI -->
        <Section title="Informasi Transaksi">
          <FieldText label="Invoice Number" mode="edit" v-model="formData.invoice_number" />
          <FieldText label="Tanggal" mode="edit" v-model="formData.date" />
          <FieldText label="Metode Pembayaran" mode="edit" v-model="formData.payment_method" />
          <FieldText label="Status Penjualan" mode="edit" v-model="formData.sales_status" />
        </Section>

        <!-- INFORMASI CUSTOMER -->
        <Section title="Informasi Customer">
          <FieldText label="Nama Customer" mode="edit" v-model="formData.customer_name" />
          <FieldText label="Kontak Customer" mode="edit" v-model="formData.customer_contact" />
          <FieldText Text label="Catatan" mode="edit" v-model="formData.notes" class="sm:col-span-2" />
        </Section>

        <!-- INFORMASI PRODUK -->
        <Section title="Informasi Produk">
          <FieldText label="Nama Produk" mode="edit" v-model="formData.product_name" />
          <FieldText label="Kode Produk" mode="edit" v-model="formData.product_code" />
          <FieldText label="Kategori" mode="edit" v-model="formData.category" />
          <FieldText label="Quantity" mode="edit" v-model="formData.quantity" />
          <FieldText label="Harga (Rp)" type="number" mode="edit" v-model="formData.price" />
          <FieldText label="Total (Rp)" :modelValue="calculatedTotal" mode="view" />

        </Section>

        <!-- PENGIRIMAN & PEMBAYARAN -->
        <Section title="Pengiriman & Pembayaran">
          <FieldText label="Kurir" mode="edit" v-model="formData.courier" />
          <FieldText label="Ongkir" mode="edit" type="number" v-model="formData.shipping_cost" />
          <FieldText label="Tracking Number" mode="edit" v-model="formData.tracking_number" />
          <FieldText Text label="Alamat Pengiriman" mode="edit" v-model="formData.shipping_address"
            class="sm:col-span-2" />
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
  name: "SalesEdit",
  components: {
    Sidebar,
    HeaderBar,
    Section,
    FieldText,
  },


  data() {
    return {
      activeMenu: "sales",
      formData: {
        invoice_number: "",
        date: "",
        payment_method: "",
        sales_status: "",
        customer_name: "",
        customer_contact: "",
        notes: "",
        product_name: "",
        product_code: "",
        category: "",
        quantity: 0,
        price: 0,
        total: 0,
        courier: "",
        shipping_cost: 0,
        tracking_number: "",
        shipping_address: ""
      }
    };
  },
  mounted() {
    this.fetchData();
  },
  computed: {
    calculatedTotal: {
      get() {
        const qty = Number(this.formData.quantity || 0);
        const price = Number(this.formData.price || 0);
        return qty * price;
      },
      set(val) {
        this.formData.total = val;
      }
    }
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

        const data = res.data.data;

        Object.assign(this.formData, {
          invoice_number: data.invoice_number,
          date: data.date,
          payment_method: data.payment_method,
          sales_status: data.sales_status,
          customer_name: data.customer_name,
          customer_contact: data.customer_contact,
          notes: data.notes,
          quantity: data.quantity,
          courier: data.courier,
          shipping_cost: data.shipping_cost,
          tracking_number: data.tracking_number,
          shipping_address: data.shipping_address,

          // ⬇️ INI KUNCINYA
          product_name: data.barang_sales?.product_name ?? "",
          product_code: data.barang_sales?.product_code ?? "",
          category: data.barang_sales?.category ?? "",
          price: data.barang_sales?.price ?? 0
        });

      } catch (e) {
        console.error("Gagal mengambil detail sales:", e);
      }
    },
    async updateData() {
      try {
        const token = localStorage.getItem("token");
        await axios.put(
          `http://localhost:8000/api/sales-edit/${this.$route.params.id}`,
          this.formData,
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );
        alert("Data sales berhasil diperbarui.");
      } catch (e) {
        console.error("Gagal memperbarui data sales:", e);
        alert("Terjadi kesalahan saat memperbarui data sales.");
      }
    },
    formatRupiah(val) {
      if (!val) return "-";
      return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
  }
};
</script>
