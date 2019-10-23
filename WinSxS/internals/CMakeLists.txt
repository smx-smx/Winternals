cmake_minimum_required(VERSION 3.0)

project(undelta)
set(CMAKE_CXX_STANDARD 17)

add_executable(undelta undelta.cpp)
target_link_libraries(undelta wcp)
target_link_directories(undelta PUBLIC ${CMAKE_CURRENT_LIST_DIR})