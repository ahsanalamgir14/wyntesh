import request from '@/utils/request'

export function fetchPendingList(query) {
  return request({
    url: '/admin/pending/kyc',
    method: 'get',
    params: query
  })
}

export function fetchRejectedList(query) {
  return request({
    url: '/admin/rejected/kyc',
    method: 'get',
    params: query
  })
}

export function fetchVerifiedList(query) {
  return request({
    url: '/admin/verified/kyc',
    method: 'get',
    params: query
  })
}

export function updateKyc(data) {
  return request({
    url: '/admin/kyc/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

// export function fetchAchiever(id) {
//   return request({
//     url: '/admin/achiever/'+id,
//     method: 'get'
//   })
// }

// export function deleteAchiever(id) {
//   return request({
//     url: '/admin/achiever/'+id+'/delete',
//     method: 'delete'
//   })
// }


// export function createAchiever(data) {
//   return request({
//     url: '/admin/achiever',
//     method: 'post',
//     data,
//     headers: { "Content-Type": "multipart/form-data" }
//   })
// }



