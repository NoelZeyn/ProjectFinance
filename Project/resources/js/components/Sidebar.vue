<template>
    <div>
        <div 
            v-if="isSidebarOpen && isMobile" 
            class="fixed inset-0 bg-black bg-opacity-50 z-[1000] transition-opacity"
            @click="toggleSidebar"
        ></div>

        <button
            v-if="!isSidebarOpen"
            class="fixed top-3 left-3 z-[1001] bg-white border border-gray-300 rounded p-2 text-gray-700 shadow-sm md:hidden hover:bg-gray-50"
            @click="toggleSidebar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <aside
            :class="[
                'fixed top-0 left-0 h-full w-[310px] bg-white border-r border-gray-200 z-[1001] transition-transform duration-300 ease-in-out flex flex-col',
                // Logika responsif: Mobile (toggle), Desktop (selalu muncul)
                isMobile && !isSidebarOpen ? '-translate-x-full' : 'translate-x-0',
                'md:translate-x-0 md:static md:h-screen'
            ]"
        >
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <img :src="logoImage" alt="Logo" class="w-[30px] h-auto object-contain" />
                    <span class="logo-text text-[20px] font-bold text-[#08607a]">Sistem Keuangan</span>
                </div>

                <button 
                    v-if="isMobile" 
                    @click="toggleSidebar"
                    class="text-gray-500 hover:text-red-500 transition-colors md:hidden"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-4 flex flex-col gap-6">
                
                <div v-if="role !== 'user_review'" class="flex flex-col gap-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-[#b0b385] mb-2">
                        Main Menu
                    </p>
                    
                    <router-link to="/dashboard" class="block">
                        <div :class="menuClass('dashboard')">
                            <img src="@/assets/dashboard.svg" class="w-5" alt="Dashboard" />
                            <span>Dashboard</span>
                        </div>
                    </router-link>

                    <router-link v-if="role === 'superadmin'" to="/manajemen-akun" class="block">
                        <div :class="menuClass('manajemenAkun')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Verifikasi" />
                            <span>Manajemen Akun</span>
                        </div>
                    </router-link>

                    <router-link
                        v-if="role === 'superadmin' || role === 'admin' || role === 'asman' || role === 'manajer' || role === 'anggaran'"
                        to="/manajemen-approval" class="block">
                        <div :class="menuClass('manajemenApproval')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Approval" />
                            <span>Manajemen Approval</span>
                        </div>
                    </router-link>

                    <router-link v-if="role !== 'user_review'" to="/manajemen-alat" class="block">
                        <div :class="menuClass('manajemenAlat')" @click="setActive('manajemenAlat')">
                            <img src="@/assets/laporan1.svg" class="w-5" alt="Iaporan" />
                            <span>Manajemen Alat</span>
                        </div>
                    </router-link>

                    <router-link v-if="role !== 'user_review'" to="/pengajuan" class="block">
                        <div :class="menuClass('pengajuan')" @click="setActive('pengajuan')">
                            <img src="@/assets/laporan1.svg" class="w-5" alt="pengajuan" />
                            <span>Pengajuan ATK</span>
                        </div>
                    </router-link>
                </div>

                <div v-if="role === 'admin' || role === 'superadmin'" class="flex flex-col gap-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-[#b0b385] mb-2">Berita Acara</p>
                    <router-link to="/surat-ba" class="block">
                        <div :class="menuClass('suratBeritaAcara')" @click="setActive('suratBeritaAcara')">
                            <img src="@/assets/person.svg" class="w-5" alt="suratBeritaAcara" />
                            <span>Surat Berita Acara</span>
                        </div>
                    </router-link>
                </div>

                <div v-if="role !== 'user'" class="flex flex-col gap-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-[#b0b385] mb-2">Laporan</p>
                    
                    <router-link to="/grafik" class="block">
                        <div :class="menuClass('grafik')" @click="setActive('grafik')">
                            <img src="@/assets/person.svg" class="w-5" alt="grafik" />
                            <span>Grafik ATK</span>
                        </div>
                    </router-link>

                    <router-link to="/laporan-ATK" class="block">
                        <div :class="menuClass('laporanATK')" @click="setActive('laporanATK')">
                            <img src="@/assets/person.svg" class="w-5" alt="laporanATK" />
                            <span>Data ATK</span>
                        </div>
                    </router-link>

                    <router-link v-if="role !== 'user'" to="/laporan-history-atk" class="block">
                        <div :class="menuClass('laporanHistoryATK')" @click="setActive('laporanHistoryATK')">
                            <img src="@/assets/person.svg" class="w-5" alt="laporanHistoryATK" />
                            <span>Riwayat Manajemen ATK</span>
                        </div>
                    </router-link>

                    <router-link v-if="role !== 'user'" to="/laporan-pemakaian" class="block">
                        <div :class="menuClass('laporanPemakaian')" @click="setActive('laporanPemakaian')">
                            <img src="@/assets/person.svg" class="w-5" alt="laporanPemakaian" />
                            <span>Riwayat Pemakaian ATK</span>
                        </div>
                    </router-link>

                    <router-link v-if="role !== 'user'" to="/laporan-approval" class="block">
                        <div :class="menuClass('laporanApproval')" @click="setActive('laporanApproval')">
                            <img src="@/assets/person.svg" class="w-5" alt="laporanApproval" />
                            <span>Riwayat Approval ATK</span>
                        </div>
                    </router-link>

                    <router-link to="/laporan-pengajuan" class="block">
                        <div :class="menuClass('laporanPengajuan')" @click="setActive('laporanPengajuan')">
                            <img src="@/assets/person.svg" class="w-5" alt="laporanPengajuan" />
                            <span>Laporan Pengajuan ATK</span>
                        </div>
                    </router-link>
                </div>

                <div class="flex flex-col gap-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-[#b0b385] mb-2">Penjualan</p>
                    <router-link to="/sales-main" class="block">
                        <div :class="menuClass('sales-main')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Sales" />
                            <span>Sales</span>
                        </div>
                    </router-link>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-[#b0b385] mb-2">Keuangan</p>
                    <router-link to="/finance-main" class="block">
                        <div :class="menuClass('finance-main')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Finance" />
                            <span>Track Finance</span>
                        </div>
                    </router-link>
                    <router-link to="/finance-stock" class="block">
                        <div :class="menuClass('finance-stock')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Finance" />
                            <span>Stock Simulation</span>
                        </div>
                    </router-link>
                    <router-link to="/finance-invest" class="block">
                        <div :class="menuClass('finance-invest')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Finance" />
                            <span>Invest Simulation</span>
                        </div>
                    </router-link>
                </div>

                <div class="flex flex-col gap-1 pb-6">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-[#b0b385] mb-2">Admin</p>
                    
                    <router-link to="/profile" class="block">
                        <div :class="menuClass('profile')">
                            <img src="@/assets/profil.svg" class="w-5" alt="Profile" />
                            <span>Profile</span>
                        </div>
                    </router-link>

                    <div 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600 cursor-pointer transition-colors"
                        @click="showModalConfirm = true"
                    >
                        <img src="@/assets/SignOut.svg" class="w-5" alt="Keluar" />
                        <span>Keluar</span>
                    </div>
                </div>

            </div>
        </aside>

        <ModalConfirm :visible="showModalConfirm" title="Konfirmasi Logout"
            message="Apakah Anda yakin ingin keluar?" @confirm="logout" @cancel="showModalConfirm = false" />
    </div>
</template>

<script>
import axios from "axios";
import logoImage from "@/assets/PLN.svg";
import ModalConfirm from "@/components/ModalConfirm.vue";

export default {
    name: "Sidebar",
    components: { ModalConfirm },
    props: ["activeMenu"],
    emits: ["update:activeMenu"],
    data() {
        const storedUser = localStorage.getItem("user");
        const parsedUser = storedUser ? JSON.parse(storedUser) : {};
        return {
            logoImage,
            showModalConfirm: false,
            role: parsedUser.tingkatan_otoritas || "",
            // Ubah logika default agar responsif
            isSidebarOpen: window.innerWidth >= 768, 
            isMobile: window.innerWidth < 768,
        };
    },
    mounted() {
        window.addEventListener("resize", this.handleResize);
        // Panggil sekali saat mount untuk set state awal
        this.handleResize(); 
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.handleResize);
    },
    methods: {
        setActive(menu) {
            this.$emit("update:activeMenu", menu);
            // Tambahan: Tutup sidebar otomatis di mobile setelah klik menu
            if (this.isMobile) {
                this.isSidebarOpen = false;
            }
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },
        // Logika resize yang lebih robust
        handleResize() {
            const wasMobile = this.isMobile;
            this.isMobile = window.innerWidth < 768;

            if (!wasMobile && this.isMobile) {
                // Desktop -> Mobile: Tutup sidebar biar ga menuhin layar
                this.isSidebarOpen = false;
            } else if (wasMobile && !this.isMobile) {
                // Mobile -> Desktop: Buka sidebar (standard desktop behavior)
                this.isSidebarOpen = true;
            }
        },
        menuClass(name) {
            const isActive = this.activeMenu === name;
            return [
                "flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer",
                isActive
                    ? "bg-[#e0f2fe] text-[#08607a]" // Warna aktif (sesuaikan jika ingin kembali ke warna lama)
                    : "text-gray-600 hover:bg-gray-50 hover:text-gray-900",
            ];
        },
        logout() {
            axios
                .post(
                    "http://localhost:8000/api/logout",
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${localStorage.getItem("token")}`,
                        },
                    }
                )
                .then(() => {
                    localStorage.clear();
                    sessionStorage.clear();
                    this.showModalConfirm = false;
                    this.$router.push("/login");
                })
                .catch((error) => {
                    console.error("Logout gagal:", error);
                    // Force logout jika API error
                    localStorage.clear();
                    this.$router.push("/login");
                });
        },
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Protest+Strike&display=swap');

.logo-text {
    font-family: "Protest Strike", sans-serif;
}

/* Custom scrollbar supaya manis */
aside .flex-1::-webkit-scrollbar {
    width: 4px;
}
aside .flex-1::-webkit-scrollbar-track {
    background: transparent;
}
aside .flex-1::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>