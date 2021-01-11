import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/stock-logs',
    method: 'get',
    params: query
  })
}
export function fetchProductVariant(query) {
  return request({
    url: '/admin/product-variants',
    method: 'get',
    params: query
  })
}
export function addStock(data) {
  return request({
    url: '/admin/stock/add',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}