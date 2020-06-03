import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/superadmin/payment-modes',
    method: 'get',
    params: query
  })
}

export function fetchPaymentMode(id) {
  return request({
    url: '/superadmin/payment-mode/'+id,
    method: 'get'
  })
}

export function deletePaymentMode(id) {
  return request({
    url: '/superadmin/payment-mode/'+id+'/delete',
    method: 'delete'
  })
}


export function createPaymentMode(data) {
  return request({
    url: '/superadmin/payment-mode',
    method: 'post',
    data
  })
}

export function updatePaymentMode(data) {
  return request({
    url: '/superadmin/payment-mode/update',
    method: 'post',
    data
  })
}

