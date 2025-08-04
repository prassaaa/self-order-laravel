<template>
  <div class="min-h-screen bg-background">
    <SidebarProvider>
      <AppSidebar />
      <SidebarInset>
        <!-- Header -->
        <header class="flex h-16 shrink-0 items-center gap-2 border-b px-4">
          <SidebarTrigger class="-ml-1" />
          <Separator orientation="vertical" class="mr-2 h-4" />
          <Breadcrumbs :breadcrumbs="breadcrumbs" />

          <div class="ml-auto flex items-center gap-2">
            <!-- Notifications -->
            <Button variant="ghost" size="sm" class="relative">
              <Bell class="h-4 w-4" />
              <Badge
                v-if="notificationCount > 0"
                class="absolute -top-1 -right-1 h-5 w-5 rounded-full p-0 flex items-center justify-center text-xs"
              >
                {{ notificationCount }}
              </Badge>
            </Button>

            <!-- User Menu -->
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm" class="relative h-8 w-8 rounded-full">
                  <Avatar class="h-8 w-8">
                    <AvatarImage :src="user?.avatar || ''" :alt="user?.name || 'User'" />
                    <AvatarFallback>{{ userInitials }}</AvatarFallback>
                  </Avatar>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56" align="end" forceMount>
                <DropdownMenuLabel class="font-normal">
                  <div class="flex flex-col space-y-1">
                    <p class="text-sm font-medium leading-none">{{ user?.name }}</p>
                    <p class="text-xs leading-none text-muted-foreground">
                      {{ user?.email }}
                    </p>
                  </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="router.visit('/settings')">
                  <Settings class="mr-2 h-4 w-4" />
                  <span>Settings</span>
                </DropdownMenuItem>
                <DropdownMenuItem @click="router.visit('/profile')">
                  <User class="mr-2 h-4 w-4" />
                  <span>Profile</span>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="logout">
                  <LogOut class="mr-2 h-4 w-4" />
                  <span>Log out</span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 space-y-4 p-4 md:p-6">
          <slot />
        </main>
      </SidebarInset>
    </SidebarProvider>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import {
  SidebarProvider,
  SidebarInset,
  SidebarTrigger,
} from '@/components/ui/sidebar'
import { Separator } from '@/components/ui/separator'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Bell, Settings, LogOut } from 'lucide-vue-next'
import AppSidebar from '@/components/admin/AppSidebar.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import type { User } from '@/types'

const page = usePage()
const user = computed((): User | undefined => (page.props as any).auth?.user)

const breadcrumbs = computed(() => {
  const routeName = (page.props as any).ziggy?.route?.name || ''

  const breadcrumbMap: Record<string, Array<{title: string, href?: string}>> = {
    'dashboard': [
      { title: 'Dashboard' }
    ],
    'admin.orders.index': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Orders' }
    ],
    'admin.menus.index': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Menu Items' }
    ],
    'admin.categories.index': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Categories' }
    ],
    'admin.reports.index': [
      { title: 'Dashboard', href: '/dashboard' },
      { title: 'Reports' }
    ],
  }

  return breadcrumbMap[routeName] || [{ title: 'Dashboard' }]
})

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map((n: string) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

// Mock notification count - in real app this would come from store
const notificationCount = computed(() => 3)

const logout = () => {
  router.post('/logout')
}
</script>
