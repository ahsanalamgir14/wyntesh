import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/superadmin/transaction-types',
    method: 'get',
    params: query
  })
}

export function fetchTransactionType(id) {
  return request({
    url: '/superadmin/transaction-type/'+id,
    method: 'get'
  })
}

export function deleteTransactionType(id) {
  return request({
    url: '/superadmin/transaction-type/'+id+'/delete',
    method: 'delete'
  })
}


export function createTransactionType(data) {
  return request({
    url: '/superadmin/transaction-type',
    method: 'post',
    data
  })
}

export function updateTransactionType(data) {
  return request({
    url: '/superadmin/transaction-type/update',
    method: 'post',
    data
  })
}

