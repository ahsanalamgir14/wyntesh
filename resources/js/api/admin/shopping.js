import request from '@/utils/request'


export function removeFromCart(id) {
  return request({
    url: '/user/cart/product/'+id+'/remove',
    method: 'delete'
  })
}

export function updateOrder(data) {
  return request({
    url: '/admin/order/update',
    method: 'post',
    data
  })
}

export function getNewOrders(query) {
  return request({
    url: '/admin/orders/new',
    method: 'get',
    params: query
  })
}

export function getAllOrders(query) {
  return request({
    url: '/admin/orders/all',
    method: 'get',
    params: query
  })
}

export function getGSTReport(query) {
  return request({
    url: '/admin/gst/report',
    method: 'get',
    params: query
  })
}
