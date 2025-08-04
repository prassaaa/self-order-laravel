<template>
  <Sidebar variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="route('dashboard')">
              <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                <UtensilsCrossed class="size-4" />
              </div>
              <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-semibold">Self Order</span>
                <span class="truncate text-xs">Admin Dashboard</span>
              </div>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <!-- Main Navigation -->
      <SidebarGroup>
        <SidebarGroupLabel>Dashboard</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton as-child :is-active="route().current('dashboard')">
              <Link :href="route('dashboard')">
                <BarChart3 />
                <span>Analytics</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <!-- Orders Management -->
      <SidebarGroup>
        <SidebarGroupLabel>Orders</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton as-child :is-active="route().current('admin.orders.*')">
              <Link :href="route('admin.orders.index')">
                <ShoppingBag />
                <span>All Orders</span>
                <Badge v-if="pendingOrdersCount > 0" variant="destructive" class="ml-auto">
                  {{ pendingOrdersCount }}
                </Badge>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
          <SidebarMenuItem>
            <SidebarMenuButton as-child>
              <Link :href="route('admin.orders.index', { status: 'pending' })">
                <Clock />
                <span>Pending</span>
                <Badge v-if="pendingOrdersCount > 0" variant="secondary" class="ml-auto">
                  {{ pendingOrdersCount }}
                </Badge>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
          <SidebarMenuItem>
            <SidebarMenuButton as-child>
              <Link :href="route('admin.orders.index', { status: 'preparing' })">
                <ChefHat />
                <span>Preparing</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
          <SidebarMenuItem>
            <SidebarMenuButton as-child>
              <Link :href="route('admin.orders.index', { status: 'ready' })">
                <Bell />
                <span>Ready</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <!-- Menu Management -->
      <SidebarGroup>
        <SidebarGroupLabel>Menu</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton as-child :is-active="route().current('admin.categories.*')">
              <Link :href="route('admin.categories.index')">
                <FolderOpen />
                <span>Categories</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
          <SidebarMenuItem>
            <SidebarMenuButton as-child :is-active="route().current('admin.menus.*')">
              <Link :href="route('admin.menus.index')">
                <UtensilsCrossed />
                <span>Menu Items</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <!-- Reports -->
      <SidebarGroup>
        <SidebarGroupLabel>Reports</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton as-child :is-active="route().current('admin.reports.*')">
              <Link :href="route('admin.reports.index')">
                <FileText />
                <span>Sales Reports</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
          <SidebarMenuItem>
            <SidebarMenuButton as-child>
              <Link :href="route('admin.reports.popular')">
                <TrendingUp />
                <span>Popular Items</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <!-- User Management (Admin only) -->
      <SidebarGroup v-if="canManageUsers">
        <SidebarGroupLabel>Users</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton as-child :is-active="route().current('admin.users.*')">
              <Link :href="route('admin.users.index')">
                <Users />
                <span>Staff Management</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <!-- Settings -->
      <SidebarGroup>
        <SidebarGroupLabel>Settings</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton as-child :is-active="route().current('admin.settings.*')">
              <Link :href="route('admin.settings.index')">
                <Settings />
                <span>System Settings</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton as-child>
            <Link :href="route('home')" target="_blank">
              <ExternalLink />
              <span>View Customer Site</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarFooter>
  </Sidebar>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import { Badge } from '@/components/ui/badge'
import {
  BarChart3,
  ShoppingBag,
  Clock,
  ChefHat,
  Bell,
  FolderOpen,
  UtensilsCrossed,
  FileText,
  TrendingUp,
  Users,
  Settings,
  ExternalLink,
} from 'lucide-vue-next'
import type { User } from '@/types'

const page = usePage()
const user = computed((): User | undefined => (page.props as any).auth?.user)

const canManageUsers = computed(() => {
  return user.value?.roles?.includes('admin')
})

// Mock data - in real app this would come from store or props
const pendingOrdersCount = computed(() => 5)
</script>
