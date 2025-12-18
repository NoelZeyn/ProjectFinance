<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 flex flex-col bg-white overflow-auto">
      <HeaderBar title="Detail Track Finance" />
      <div class="border-b border-gray-300"></div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl shadow mt-6">
        <h3 class="text-[15px] text-[#074a5d] font-semibold mb-4">
          Informasi Track Finance
        </h3>

        <div class="h-[1px] w-full bg-gray-300 my-4"></div>

        <div class="flex flex-col gap-6">
          <h4 class="text-[15px] font-medium text-black text-center">
            Form Informasi Track Finance
          </h4>

          <template v-for="field in fields" :key="field.key">
            <div class="flex flex-col gap-1">
              <div class="flex flex-col sm:flex-row gap-3">
                <label class="sm:w-[150px] font-semibold text-sm">
                  {{ field.label }}
                </label>

                <!-- TEXTAREA -->
                <textarea
                  v-if="field.type === 'textarea'"
                  v-model="formData[field.key]"
                  readonly
                  class="w-full p-2 border rounded-lg bg-gray-200 text-sm cursor-not-allowed"
                />

                <!-- INPUT -->
                <input
                  v-else
                  :type="field.type"
                  v-model="formData[field.key]"
                  readonly
                  class="w-full p-2 border rounded-lg text-sm bg-gray-200 cursor-not-allowed border-gray-300"
                />
              </div>
            </div>
          </template>

          <div class="flex justify-between mt-6">
            <router-link to="/finance-main">
              <button
                class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                Kembali
              </button>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import axios from "axios";

export default {
  name: "TrackDetail",
  components: { Sidebar, HeaderBar },

  data() {
    return {
      activeMenu: "manajemenAlat",
      formData: {
        item: "",
        date: "",
        category: "",
        description: "",
        amount: "",
        price: "",
        total: 0,
      },
      fields: [
        { key: "item", label: "Nama Barang", type: "text" },
        { key: "date", label: "Tanggal", type: "date" },
        { key: "category", label: "Kategori", type: "text" },
        { key: "description", label: "Deskripsi", type: "textarea" },
        { key: "amount", label: "Jumlah", type: "number" },
        { key: "price", label: "Harga (Rp)", type: "number" },
        { key: "total", label: "Total Harga (Rp)", type: "number" },
      ],
    };
  },

  mounted() {
    this.fetchTracks();
  },

  methods: {
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

        if (response.data?.data) {
          this.formData = response.data.data;
        }
      } catch (error) {
        console.error("Gagal mengambil data finance:", error);
      }
    },
  },
};
</script>
