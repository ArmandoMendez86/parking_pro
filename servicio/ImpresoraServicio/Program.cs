using System;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Printing;
using System.IO;
using System.Net;
using System.Text;
using Newtonsoft.Json;

public class BloqueImpresion {
    public string Texto { get; set; } = "";
    public string Alineacion { get; set; } = "left";
    public int Size { get; set; } = 10;
}

public class TicketRequest {
    public string Impresora { get; set; } = "";
    public int Ancho { get; set; }
    public List<BloqueImpresion> Bloques { get; set; } = new List<BloqueImpresion>();
}

class Program {
    static TicketRequest? requestActual;

    static void Main(string[] args) {
        HttpListener listener = new HttpListener();
        listener.Prefixes.Add("http://*:8080/imprimir/");
        
        try {
            listener.Start();
            Console.WriteLine("Motor de Impresión Activo en puerto 8080...");
        } catch (Exception ex) {
            Console.WriteLine("Error: Inicia como administrador. " + ex.Message);
            return;
        }

        while (true) {
            HttpListenerContext context = listener.GetContext();
            HttpListenerResponse res = context.Response;

            res.AppendHeader("Access-Control-Allow-Origin", "*");
            res.AppendHeader("Access-Control-Allow-Methods", "POST, OPTIONS");
            res.AppendHeader("Access-Control-Allow-Headers", "Content-Type");

            if (context.Request.HttpMethod == "OPTIONS") {
                res.StatusCode = 200;
                res.OutputStream.Close();
                continue;
            }

            if (context.Request.HttpMethod == "POST") {
                try {
                    using var reader = new StreamReader(context.Request.InputStream);
                    string json = reader.ReadToEnd();
                    requestActual = JsonConvert.DeserializeObject<TicketRequest>(json);

                    if (requestActual != null) {
                        Console.WriteLine($"Imprimiendo en: {requestActual.Impresora}");
                        ImprimirFisico();
                    }
                } catch (Exception ex) {
                    Console.WriteLine("Error al procesar: " + ex.Message);
                }
            }
            
            byte[] buffer = Encoding.UTF8.GetBytes("{\"exito\":true}");
            res.OutputStream.Write(buffer, 0, buffer.Length);
            res.OutputStream.Close();
        }
    }

    static void ImprimirFisico() {
        if (requestActual == null) return;
        PrintDocument pd = new PrintDocument();
        pd.PrinterSettings.PrinterName = requestActual.Impresora;

        pd.PrintPage += (sender, ev) => {
            Graphics g = ev.Graphics;
            float y = 10;
            float anchoMax = (requestActual.Ancho == 80) ? 280 : 180;

            foreach (var b in requestActual.Bloques) {
                if (string.IsNullOrEmpty(b.Texto)) continue;

                Font f = new Font("Courier New", b.Size, FontStyle.Bold);
                StringFormat sf = new StringFormat();
                
                if (b.Alineacion == "center") sf.Alignment = StringAlignment.Center;
                else if (b.Alineacion == "right") sf.Alignment = StringAlignment.Far;
                else sf.Alignment = StringAlignment.Near;

                SizeF size = g.MeasureString(b.Texto, f, (int)anchoMax, sf);
                RectangleF rect = new RectangleF(5, y, anchoMax, size.Height + 5);
                
                g.DrawString(b.Texto, f, Brushes.Black, rect, sf);
                y += size.Height + 5;
            }
        };
        pd.Print();
    }
}