using System;
using System.Globalization;
using System.Threading.Tasks;
using Avalonia;
using Avalonia.Controls;
using Avalonia.Interactivity;
using Avalonia.Layout;
using Avalonia.Media;

namespace CalculatorApp;

public partial class MainWindow : Window
{
    // Variabel untuk perhitungan
    double firstNumber = 0;
    double secondNumber = 0;
    double result = 0;
    string operation = "";

    public MainWindow()
    {
        InitializeComponent();
    }

    // Event untuk semua tombol angka (0-9)
    private void NumberButton_Click(object? sender, RoutedEventArgs e)
    {
        if (sender is not Button button) return;
        string angka = button.Content?.ToString() ?? "";
        string display = txtDisplay.Text ?? "";

        if (display == "0")
            txtDisplay.Text = angka;
        else
            txtDisplay.Text = display + angka;
    }

    // Event untuk semua tombol operator (+, −, ×, ÷)
    private async void OperatorButton_Click(object? sender, RoutedEventArgs e)
    {
        if (sender is not Button button) return;

        if (!double.TryParse(txtDisplay.Text, NumberStyles.Float, CultureInfo.InvariantCulture, out double angka))
        {
            await ShowMessage("Masukkan angka terlebih dahulu.", "Peringatan");
            return;
        }

        firstNumber = angka;
        operation = button.Content?.ToString() ?? "";
        txtDisplay.Text = "";
    }

    // Event tombol "=" untuk menghitung hasil
    private async void btnEquals_Click(object? sender, RoutedEventArgs e)
    {
        if (operation == "") return;

        try
        {
            secondNumber = double.Parse(txtDisplay.Text ?? "", CultureInfo.InvariantCulture);

            switch (operation)
            {
                case "+":
                    result = firstNumber + secondNumber;
                    break;
                case "−":
                    result = firstNumber - secondNumber;
                    break;
                case "×":
                    result = firstNumber * secondNumber;
                    break;
                case "÷":
                    if (secondNumber == 0)
                        throw new DivideByZeroException("Tidak dapat membagi dengan nol.");
                    result = firstNumber / secondNumber;
                    break;
            }

            txtDisplay.Text = result.ToString(CultureInfo.InvariantCulture);
            operation = "";
        }
        catch (DivideByZeroException ex)
        {
            txtDisplay.Text = "0";
            await ShowMessage(ex.Message, "Error");
        }
        catch (FormatException)
        {
            await ShowMessage("Input tidak valid. Masukkan angka yang benar.", "Error");
        }
        catch (Exception ex)
        {
            await ShowMessage(ex.Message, "Error");
        }
    }

    // Event tombol Clear
    private void btnClear_Click(object? sender, RoutedEventArgs e)
    {
        firstNumber = 0;
        secondNumber = 0;
        result = 0;
        operation = "";
        txtDisplay.Text = "0";
    }

    // Event tombol Decimal
    private void btnDecimal_Click(object? sender, RoutedEventArgs e)
    {
        string display = txtDisplay.Text ?? "";
        if (display == "")
            txtDisplay.Text = "0.";
        else if (!display.Contains("."))
            txtDisplay.Text = display + ".";
    }

    // Pengganti MessageBox.Show() pada Windows Forms
    private async Task ShowMessage(string message, string title)
    {
        var okButton = new Button
        {
            Content = "OK",
            Width = 90,
            HorizontalAlignment = HorizontalAlignment.Center
        };

        var dialog = new Window
        {
            Title = title,
            Width = 320,
            SizeToContent = SizeToContent.Height,
            CanResize = false,
            WindowStartupLocation = WindowStartupLocation.CenterOwner,
            Content = new StackPanel
            {
                Margin = new Thickness(20),
                Spacing = 16,
                Children =
                {
                    new TextBlock
                    {
                        Text = message,
                        TextWrapping = TextWrapping.Wrap
                    },
                    okButton
                }
            }
        };

        okButton.Click += (_, _) => dialog.Close();
        await dialog.ShowDialog(this);
    }
}