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

export function deleteCategory(id) {
  return request({
    url: '/admin/categories/'+id+'/delete',
    method: 'delete'
  })
}


export function createCategory(data) {
  return request({
    url: '/admin/categories',
    method: 'post',
    data
  })
}

export function updateCategory(data) {
  return request({
    url: '/admin/categories/update',
    method: 'post',
    data
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

export function deleteProduct(id) {
  return request({
    url: '/admin/products/'+id+'/delete',
    method: 'delete'
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

export function deleteProductImage(id) {
  return request({
    url: '/admin/products/image/'+id+'/delete',
    method: 'delete'
  })
}

