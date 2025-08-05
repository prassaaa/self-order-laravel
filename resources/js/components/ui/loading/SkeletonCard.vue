<template>
  <div class="bg-card rounded-lg border overflow-hidden">
    <!-- Image skeleton -->
    <div class="aspect-video bg-gradient-to-r from-muted via-muted/50 to-muted relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
    </div>
    
    <!-- Content skeleton -->
    <div class="p-4 space-y-3">
      <!-- Category -->
      <div class="h-3 bg-gradient-to-r from-muted via-muted/50 to-muted rounded-full w-1/4 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
      </div>
      
      <!-- Title -->
      <div class="h-5 bg-gradient-to-r from-muted via-muted/50 to-muted rounded w-3/4 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
      </div>
      
      <!-- Description -->
      <div class="space-y-2">
        <div class="h-4 bg-gradient-to-r from-muted via-muted/50 to-muted rounded w-full relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
        </div>
        <div class="h-4 bg-gradient-to-r from-muted via-muted/50 to-muted rounded w-2/3 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
        </div>
      </div>
      
      <!-- Price and button -->
      <div class="flex items-center justify-between pt-2">
        <div class="h-6 bg-gradient-to-r from-muted via-muted/50 to-muted rounded w-1/3 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
        </div>
        <div class="h-8 bg-gradient-to-r from-muted via-muted/50 to-muted rounded w-20 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// No props needed for this skeleton
</script>

<style scoped>
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 2s infinite;
}
</style>
