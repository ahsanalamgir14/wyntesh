import request from '@/utils/request'

export function getAllCategories() {
    return request({
        url: '/user/categories/all',
        method: 'get'
    })
}
export function getColors() {
    return request({
        url: '/user/getcolors',
        method: 'get',
    })
}
export function getSizes() {
    return request({
        url: '/user/getsizes',
        method: 'get',
    })
}
export function getSizeByColor(query) {
    return request({
        url: '/user/getsizebycolor/',
        method: 'get',
        params: query
    })
}

export function getColorBySize(id) {
    return request({
        url: '/user/getcolorbysize/' + id,
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

export function getColorsByCategory(categoryId) {
    return request({
        url: '/user/get-category-colors/' + categoryId,
        method: 'get',
    })
}
export function getSizesByCategory(categoryId) {
    return request({
        url: '/user/get-category-sizes/' + categoryId,
        method: 'get',
    })
}

export function getStock(query) {
    return request({
        url: '/user/getstock',
        method: 'get',
        params: query
    })
}

export function getProduct(id) {
    return request({
        url: '/user/product/' + id,
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
        url: '/user/cart/product/' + id + '/remove',
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

export function getOrder(id) {
    return request({
        url: '/user/order/' + id,
        method: 'get'
    })
}

export function getGSTReport(query) {
    return request({
        url: '/user/gst-report',
        method: 'get',
        params: query
    })
}

export function getPersonalPVMonthly(id) {
    return request({
        url: '/user/personal-pv-monthly/',
        method: 'get'
    })
}