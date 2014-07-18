' Author: Veck Hsiao 2014/02 @ PLSM, CS, NCCU, Taiwan
' Usage: Count the 3 metrics (Likes, Shares, Comments) by Page of Facebook data set.

Sub test()
    Dim likeSum As Long     '用 Integer 會溢位 (89802 就會溢位)
    Dim commentSum As Long
    Dim shareSum As Long
    Dim j As Integer    'for sheet 粉絲頁發文數
    Dim source As String
    Dim target As String
    Dim page_column As Integer

    source = "[table_tobe_counted]"
    target = "[table_of_result]"

    page_column = [column_of_page_name]
    
    j = 2               'From 2 因為 1 是 column title
    
    For i = 1 To [final_row]     '這裡 From 1 為了要在第一次先執行 Else 讓 sum 為第一個 Page Name 的第一個 按讚數
    
        If Worksheets(source).Cells(i, page_column) = Worksheets(source).Cells(i + 1, page_column) Then
            likeSum = likeSum + Worksheets(source).Cells(i + 1, 6)         '統計按讚數
            commentSum = commentSum + Worksheets(source).Cells(i + 1, 7)   '統計回應數
            shareSum = shareSum + Worksheets(source).Cells(i + 1, 8)       '統計分享數
            
        Else    '換下一個 Page Name
            If Not i = 1 Then
               Worksheets(target).Cells(j, 1) = Worksheets(source).Cells(i, page_column)    '寫入Page Name
               Worksheets(target).Cells(j, 3) = likeSum    '寫入按讚數
               Worksheets(target).Cells(j, 4) = commentSum '寫入回應數
               Worksheets(target).Cells(j, 5) = shareSum   '寫入分享數
               j = j + 1
            End If
            
            '紀錄新 Page Name 的第一筆
            likeSum = Worksheets(source).Cells(i + 1, 6)
            commentSum = Worksheets(source).Cells(i + 1, 7)
            shareSum = Worksheets(source).Cells(i + 1, 8)
        End If
    Next i
End Sub
