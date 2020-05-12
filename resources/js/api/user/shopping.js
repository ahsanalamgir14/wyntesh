import request from '@/utils/request'

export function getAllCategories() {
  return request({
    url: '/user/categories/all',
    method: 'get'
  })
}

export function fetchProducts(query) {
  return request({
    url: '/user/products',
    method: 'get',
    params: query
  })
}

export function getProduct(id) {
  return request({
    url: '/user/products/'+id,
    method: 'get'
  })
}

export function addToCart(data) {
  return request({
    url: '/user/cart/add/product',
    method: 'post',
    data
  })
}

export function getMyCartProducts() {
  return request({
    url: '/user/my/cart/products/',
    method: 'get'
  })
}

export function removeFromCart(id) {
  return request({
    url: '/user/cart/product/'+id+'/remove',
    method: 'delete'
  })
}

