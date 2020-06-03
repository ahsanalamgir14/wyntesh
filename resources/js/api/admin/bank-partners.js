import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/bank-partners',
    method: 'get',
    params: query
  })
}

export function fetchBankPartner(id) {
  return request({
    url: '/admin/bank-partner/'+id,
    method: 'get'
  })
}

export function deleteBankPartner(id) {
  return request({
    url: '/admin/bank-partner/'+id+'/delete',
    method: 'delete'
  })
}


export function createBankPartner(data) {
  return request({
    url: '/admin/bank-partner',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateBankPartner(data) {
  return request({
    url: '/admin/bank-partner/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

