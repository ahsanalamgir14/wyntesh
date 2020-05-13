import request from '@/utils/request'

export function fetchAddresses(query) {
  return request({
    url: '/user/addresses',
    method: 'get',
    params: query
  })
}

export function getAllAddresses() {
  return request({
    url: '/user/addresses/all',
    method: 'get'
  })
}

export function deleteAddress(id) {
  return request({
    url: '/user/address/'+id+'/delete',
    method: 'delete'
  })
}


export function createAddress(data) {
  return request({
    url: '/user/address',
    method: 'post',
    data
  })
}

export function updateAddress(data) {
  return request({
    url: '/user/address/update',
    method: 'post',
    data
  })
}
