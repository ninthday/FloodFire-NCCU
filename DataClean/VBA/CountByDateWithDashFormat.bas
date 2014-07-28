'CountByDate with format: yyyy-mm-dd [hh:mm:ss]
Sub post()
    Dim i As Integer
    Dim WriteIndex As Integer
    Dim sum As Integer
    Dim source As String
    Dim target As String
    Dim date_column As Integer

    Dim pre() As String
    Dim post() As String


    source = "YourTwapperExcelView"
    target = "count"
    date_column = 13

    WriteIndex = 2  '寫入 發文數時間序列
    sum = 1         'sum 從 1 開始是因為我們是從每個日期的第二筆開始計算累加，如果 sum 一開始是 0 就會少算第一筆
    
    For i = 3 To 14453 '從 3 開始是因為我 If 是判斷目前這個與前一格的日期是否相同，這樣做是因為當跨日期時，就可以停下來
        pre = Split(Worksheets(source).Cells(i, date_column))
        post = Split(Worksheets(source).Cells(i - 1, date_column))
        If pre(0) = post(0) Then
            sum = sum + 1
            If i = 14453 Then    '因為最後一筆跑完以後，就無法在到下一筆去判斷為Else然後寫入
                Worksheets(target).Cells(WriteIndex, 2) = sum
            End If
        Else
            Worksheets(target).Cells(WriteIndex, 2) = sum  '寫入統計結果
            sum = 1
            WriteIndex = WriteIndex + 1
        End If
        
    Next i
End Sub