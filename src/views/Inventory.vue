<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Inventory / Products</h2>
        <p class="text-sm text-gray-500 mt-1">Manage products and track stock levels</p>
      </div>
      <button @click="showModal = true" class="btn btn-primary">
        <PlusIcon class="h-5 w-5 inline mr-2" />
        Add Product
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="card">
        <p class="text-sm font-medium text-gray-600">Total Products</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ products.length }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">Total Value</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ formatCurrency(totalValue) }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
        <p class="mt-2 text-2xl font-bold text-yellow-600">{{ lowStockCount }}</p>
      </div>
      <div class="card">
        <p class="text-sm font-medium text-gray-600">Out of Stock</p>
        <p class="mt-2 text-2xl font-bold text-red-600">{{ outOfStockCount }}</p>
      </div>
    </div>

    <!-- Products Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Product Name</th>
              <th>Category</th>
              <th class="text-right">Cost</th>
              <th class="text-right">Price</th>
              <th class="text-right">Stock</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in products" :key="product.id">
              <td class="font-mono text-sm">{{ product.code }}</td>
              <td class="font-medium">{{ product.name }}</td>
              <td>{{ product.category }}</td>
              <td class="text-right">{{ formatCurrency(product.cost) }}</td>
              <td class="text-right">{{ formatCurrency(product.price) }}</td>
              <td class="text-right">
                <span
                  :class="[
                    'font-medium',
                    getStockClass(product.stock, product.reorder_level),
                  ]"
                >
                  {{ product.stock }}
                </span>
              </td>
              <td>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    getStockStatusClass(product.stock, product.reorder_level),
                  ]"
                >
                  {{ getStockStatus(product.stock, product.reorder_level) }}
                </span>
              </td>
              <td class="text-right">
                <button
                  @click="editProduct(product)"
                  class="text-primary-600 hover:text-primary-700 mr-3"
                >
                  Edit
                </button>
              </td>
            </tr>
            <tr v-if="products.length === 0">
              <td colspan="8" class="text-center py-8 text-gray-500">No products found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { inventoryApi } from '@/services/api'
import { PlusIcon } from '@heroicons/vue/24/outline'

const products = ref([])
const showModal = ref(false)

const totalValue = computed(() => {
  return products.value.reduce((sum, product) => sum + product.stock * product.cost, 0)
})

const lowStockCount = computed(() => {
  return products.value.filter((p) => p.stock > 0 && p.stock <= p.reorder_level).length
})

const outOfStockCount = computed(() => {
  return products.value.filter((p) => p.stock === 0).length
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount || 0)
}

const getStockClass = (stock, reorderLevel) => {
  if (stock === 0) return 'text-red-600'
  if (stock <= reorderLevel) return 'text-yellow-600'
  return 'text-green-600'
}

const getStockStatus = (stock, reorderLevel) => {
  if (stock === 0) return 'Out of Stock'
  if (stock <= reorderLevel) return 'Low Stock'
  return 'In Stock'
}

const getStockStatusClass = (stock, reorderLevel) => {
  if (stock === 0) return 'bg-red-100 text-red-800'
  if (stock <= reorderLevel) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

const loadProducts = async () => {
  try {
    const response = await inventoryApi.getProducts()
    products.value = response.data || []
  } catch (error) {
    console.error('Error loading products:', error)
    products.value = []
  }
}

const editProduct = (product) => {
  showModal.value = true
}

onMounted(() => {
  loadProducts()
})
</script>
