import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/payout-types',
    method: 'get',
    params: query
  })
}

export function fetchPayoutType(id) {
  return request({
    url: '/admin/payout-type/'+id,
    method: 'get'
  })
}

export function getAllPayoutTypes(id) {
  return request({
    url: '/admin/payout-types/all',
    method: 'get'
  })
}

export function getScheduledTypes(id) {
  return request({
    url: '/admin/scheduled-types',
    method: 'get'
  })
}

export function deletePayoutType(id) {
  return request({
    url: '/admin/payout-type/'+id+'/delete',
    method: 'delete'
  })
}


export function createPayoutType(data) {
  return request({
    url: '/admin/payout-type',
    method: 'post',
    data
  })
}

export function updatePayoutType(data) {
  return request({
    url: '/admin/payout-type/update',
    method: 'post',
    data
  })
}

