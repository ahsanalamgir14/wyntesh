import request from '@/utils/request'

export function getContests(query) {
  return request({
    url: '/admin/contests',
    method: 'get',
    params: query
  })
}

export function getAllContests() {
  return request({
    url: '/admin/contests/all',
    method: 'get'
  })
}

export function fetchContest(id) {
  return request({
    url: '/admin/contest/'+id,
    method: 'get'
  })
}

export function startContest(id) {
  return request({
    url: '/admin/contest/start/'+id,
    method: 'get'
  })
}

export function deleteContest(id) {
  return request({
    url: '/admin/contest/'+id+'/delete',
    method: 'delete'
  })
}


export function createContest(data) {
  return request({
    url: '/admin/contest',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function updateContest(data) {
  return request({
    url: '/admin/contest/update',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}


export function createSpecialReward(data) {
  return request({
    url: '/admin/contest',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function deleteContestSpecialReward(id) {
  return request({
    url: '/admin/contest/special/reward/'+id+'/delete',
    method: 'delete'
  })
}

