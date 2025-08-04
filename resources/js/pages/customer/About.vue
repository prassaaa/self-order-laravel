<template>
  <CustomerLayout>
    <div class="container mx-auto px-4 py-8">
      <!-- Hero Section -->
      <section class="text-center mb-16">
        <h1 class="text-4xl lg:text-5xl font-bold mb-6">Tentang Kami</h1>
        <p class="text-xl text-muted-foreground max-w-3xl mx-auto">
          Kami adalah UMKM yang berkomitmen menyajikan hidangan berkualitas dengan
          sistem pemesanan modern yang memudahkan pelanggan.
        </p>
      </section>

      <!-- Story Section -->
      <section class="mb-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-bold mb-6">Cerita Kami</h2>
            <div class="space-y-4 text-muted-foreground">
              <p>
                Dimulai dari sebuah warung kecil di tahun 2020, kami memiliki visi untuk
                memberikan pengalaman kuliner yang tak terlupakan dengan cita rasa autentik
                dan pelayanan yang ramah.
              </p>
              <p>
                Dengan berkembangnya teknologi, kami menghadirkan sistem pemesanan mandiri
                yang memungkinkan pelanggan untuk menikmati hidangan favorit mereka dengan
                lebih mudah dan efisien.
              </p>
              <p>
                Setiap hidangan yang kami sajikan dibuat dengan bahan-bahan segar pilihan
                dan dimasak dengan penuh cinta oleh chef berpengalaman kami.
              </p>
            </div>
          </div>
          <div class="bg-muted rounded-2xl p-8 flex items-center justify-center">
            <UtensilsCrossed class="h-32 w-32 text-muted-foreground" />
          </div>
        </div>
      </section>

      <!-- Values Section -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-center mb-12">Nilai-Nilai Kami</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <Card class="text-center p-6">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
              <Heart class="h-8 w-8 text-primary-foreground" />
            </div>
            <h3 class="text-xl font-semibold mb-3">Kualitas</h3>
            <p class="text-muted-foreground">
              Kami berkomitmen menggunakan bahan-bahan berkualitas tinggi untuk
              setiap hidangan yang kami sajikan.
            </p>
          </Card>

          <Card class="text-center p-6">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
              <Users class="h-8 w-8 text-primary-foreground" />
            </div>
            <h3 class="text-xl font-semibold mb-3">Pelayanan</h3>
            <p class="text-muted-foreground">
              Kepuasan pelanggan adalah prioritas utama kami. Kami selalu berusaha
              memberikan pelayanan terbaik.
            </p>
          </Card>

          <Card class="text-center p-6">
            <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
              <Zap class="h-8 w-8 text-primary-foreground" />
            </div>
            <h3 class="text-xl font-semibold mb-3">Inovasi</h3>
            <p class="text-muted-foreground">
              Kami terus berinovasi dalam teknologi dan menu untuk memberikan
              pengalaman terbaik bagi pelanggan.
            </p>
          </Card>
        </div>
      </section>

      <!-- Contact Section -->
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-center mb-12">Hubungi Kami</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
          <!-- Contact Info -->
          <div class="space-y-6">
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                <MapPin class="h-6 w-6 text-primary-foreground" />
              </div>
              <div>
                <h3 class="font-semibold mb-1">Alamat</h3>
                <p class="text-muted-foreground">
                  Jl. Contoh No. 123<br>
                  Kelurahan Contoh, Kecamatan Contoh<br>
                  Jakarta Selatan 12345
                </p>
              </div>
            </div>

            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                <Phone class="h-6 w-6 text-primary-foreground" />
              </div>
              <div>
                <h3 class="font-semibold mb-1">Telepon</h3>
                <p class="text-muted-foreground">
                  (021) 1234-5678<br>
                  +62 812-3456-7890
                </p>
              </div>
            </div>

            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                <Clock class="h-6 w-6 text-primary-foreground" />
              </div>
              <div>
                <h3 class="font-semibold mb-1">Jam Operasional</h3>
                <div class="text-muted-foreground space-y-1">
                  <p>Senin - Jumat: 08:00 - 22:00</p>
                  <p>Sabtu - Minggu: 09:00 - 23:00</p>
                </div>
              </div>
            </div>

            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                <Mail class="h-6 w-6 text-primary-foreground" />
              </div>
              <div>
                <h3 class="font-semibold mb-1">Email</h3>
                <p class="text-muted-foreground">
                  info@selforder.com<br>
                  support@selforder.com
                </p>
              </div>
            </div>
          </div>

          <!-- Map Placeholder -->
          <div class="bg-muted rounded-lg p-8 flex items-center justify-center min-h-[300px]">
            <div class="text-center">
              <MapPin class="h-16 w-16 mx-auto text-muted-foreground mb-4" />
              <p class="text-muted-foreground">
                Peta Lokasi<br>
                <small>Integrasi dengan Google Maps</small>
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="text-center">
        <Card class="p-8 bg-primary text-primary-foreground">
          <h2 class="text-3xl font-bold mb-4">Siap Mencoba Menu Kami?</h2>
          <p class="text-lg mb-6 opacity-90">
            Jelajahi berbagai pilihan hidangan lezat dan nikmati pengalaman
            pemesanan yang mudah dan cepat.
          </p>
          <Button
            size="lg"
            variant="secondary"
            @click="router.visit('/menu')"
          >
            <UtensilsCrossed class="h-5 w-5 mr-2" />
            Lihat Menu
          </Button>
        </Card>
      </section>
    </div>
  </CustomerLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/CustomerLayout.vue'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
  UtensilsCrossed,
  Heart,
  Users,
  Zap,
  MapPin,
  Phone,
  Clock,
  Mail
} from 'lucide-vue-next'
</script>
