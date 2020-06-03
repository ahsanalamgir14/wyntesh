import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/ranks',
    method: 'get',
    params: query
  })
}

export function fetchRank(id) {
  return request({
    url: '/admin/rank/'+id,
    method: 'get'
  })
}

export function getAllRanks() {
  return request({
    url: '/admin/ranks/all',
    method: 'get'
  })
}

export function deleteRank(id) {
  return request({
    url: '/admin/rank/'+id+'/delete',
    method: 'delete'
  })
}


export function createRank(data) {
  return request({
    url: '/admin/rank',
    method: 'post',
    data
  })
}

export function updateRank(data) {
  return request({
    url: '/admin/rank/update',
    method: 'post',
    data
  })
}

