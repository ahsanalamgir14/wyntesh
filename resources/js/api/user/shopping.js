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

export function updateCartQty(data) {
  return request({
    url: '/user/cart/update/qty',
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

export function getMyCart() {
  return request({
    url: '/user/my/cart',
    method: 'get'
  })
}

export function getMyCartCount() {
  return request({
    url: '/user/my/cart/count',
    method: 'get'
  })
}

export function removeFromCart(id) {
  return request({
    url: '/user/cart/product/'+id+'/remove',
    method: 'delete'
  })
}

export function placeOrder(data) {
  return request({
    url: '/user/order/place',
    method: 'post',
    data
  })
}

export function myOrders(query) {
  return request({
    url: '/user/orders',
    method: 'get',
    params: query
  })
}
