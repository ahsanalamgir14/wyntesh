import request from '@/utils/request'

export function fetchCagegories(query) {
  return request({
    url: '/admin/categories',
    method: 'get',
    params: query
  })
}

export function getAllCategories() {
  return request({
    url: '/admin/categories/all',
    method: 'get'
  })
}

export function getAllProductVariant() {
  return request({
    url: '/admin/product-variant/all',
    method: 'get'
  })
}

export function getAllProducts() {
  return request({
    url: '/admin/products/all',
    method: 'get',
  })
}

export function getAllProductVariantList(id) {
  return request({
    url: '/admin/product-variant/'+id,
    method: 'get',
  })
}


export function getAllColorVariant() {
  return request({
    url: '/admin/color-variant/all',
    method: 'get'
  })
}
export function getAllSizeVariant() {
  return request({
    url: '/admin/size-variant/all',
    method: 'get'
  })
}

export function deleteCategory(id) {
  return request({
    url: '/admin/categories/'+id+'/delete',
    method: 'delete'
  })
}
export function changeVariantStatus(id) {
  return request({
    url: '/admin/variant/'+id+'/change-status',
    method: 'post'
  })
}

export function createCategory(data) {
  return request({
    url: '/admin/categories',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateCategory(data) {
  return request({
    url: '/admin/categories/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function fetchProducts(query) {
  return request({
    url: '/admin/products',
    method: 'get',
    params: query
  })
}

export function getProduct(id) {
  return request({
    url: '/admin/products/'+id,
    method: 'get'
  })
}

export function changeProductStatus(id) {
  return request({
    url: '/admin/products/'+id+'/change-status',    
    method: 'post'
  })
}

export function createProduct(data) {
  return request({
    url: '/admin/products',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateProduct(data) {
  return request({
    url: '/admin/products/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function uploadProductImage(data) {
  return request({
    url: '/admin/products/image/upload',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function addProductVariant(data) {
  return request({
    url: '/admin/product-variant/add',
    method: 'post',
    data
  })
}

export function deleteProductImage(id) {
  return request({
    url: '/admin/products/image/'+id+'/delete',
    method: 'delete'
  })
}

export function getProductStocks(query) {
  return request({
    url: '/admin/product/stocks',
    method: 'get',
    params: query
  })
}
